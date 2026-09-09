/**
 * Remembers the active Bootstrap tab per page (keyed by pathname) across
 * full page reloads/navigations, using sessionStorage - e.g. so adding a
 * trip expense (which redirects back to the same page) reopens on the
 * "Trip Expenses" tab instead of resetting to the first one.
 *
 * Generic: works on any page using the standard Bootstrap 3
 * `ul[role="tablist"] > li > a[data-toggle="tab"]` markup already used
 * throughout this app, keyed by each tab group's position in the page so
 * multiple independent tab widgets on one page don't collide with each
 * other. Cleared automatically when the browser tab is closed.
 */
(function ($) {
    if (!$) {
        return;
    }

    function storageKey() {
        return 'tabMemory:' + window.location.pathname;
    }

    function tabGroups() {
        return $('ul[role="tablist"]').toArray();
    }

    function restore() {
        var raw;
        try {
            raw = sessionStorage.getItem(storageKey());
        } catch (e) {
            return;
        }
        if (!raw) {
            return;
        }

        var state;
        try {
            state = JSON.parse(raw);
        } catch (e) {
            return;
        }

        tabGroups().forEach(function (ul, index) {
            var href = state[index];
            if (!href) {
                return;
            }
            var $link = $(ul).find('a[data-toggle="tab"][href="' + href + '"]');
            if ($link.length) {
                $link.tab('show');
            }
        });
    }

    function save() {
        var state = tabGroups().map(function (ul) {
            var $active = $(ul).find('> li.active > a[data-toggle="tab"]').first();
            return $active.length ? $active.attr('href') : null;
        });
        try {
            sessionStorage.setItem(storageKey(), JSON.stringify(state));
        } catch (e) {}
    }

    $(function () {
        restore();
        $(document).on('shown.bs.tab', 'a[data-toggle="tab"]', save);
    });
})(window.jQuery);
