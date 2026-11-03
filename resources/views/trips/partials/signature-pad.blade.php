@livewireScripts
<script>
  // Minimal touch/mouse signature pad for the delivery note e-sign dialog.
  (function () {
    var pad = { canvas: null, ctx: null, drawing: false, dirty: false };

    function init() {
      var canvas = document.getElementById('dnSignaturePad');
      if (!canvas || canvas === pad.canvas) return;

      var ratio = Math.max(window.devicePixelRatio || 1, 1);
      canvas.width = canvas.offsetWidth * ratio;
      canvas.height = canvas.offsetHeight * ratio;
      var ctx = canvas.getContext('2d');
      ctx.scale(ratio, ratio);
      ctx.lineWidth = 2.2;
      ctx.lineCap = 'round';
      ctx.lineJoin = 'round';
      ctx.strokeStyle = '#0b0f13';

      pad.canvas = canvas;
      pad.ctx = ctx;
      pad.dirty = false;

      function point(e) {
        var rect = canvas.getBoundingClientRect();
        return { x: e.clientX - rect.left, y: e.clientY - rect.top };
      }

      canvas.addEventListener('pointerdown', function (e) {
        e.preventDefault();
        canvas.setPointerCapture(e.pointerId);
        pad.drawing = true;
        var p = point(e);
        ctx.beginPath();
        ctx.moveTo(p.x, p.y);
        ctx.lineTo(p.x + 0.1, p.y + 0.1);
        ctx.stroke();
        pad.dirty = true;
      });
      canvas.addEventListener('pointermove', function (e) {
        if (!pad.drawing) return;
        e.preventDefault();
        var p = point(e);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
      });
      ['pointerup', 'pointercancel', 'pointerleave'].forEach(function (name) {
        canvas.addEventListener(name, function () { pad.drawing = false; });
      });
    }

    window.dnSignaturePad = {
      clear: function () {
        if (!pad.canvas) return;
        pad.ctx.clearRect(0, 0, pad.canvas.width, pad.canvas.height);
        pad.dirty = false;
      },
      save: function (button) {
        var empty = document.getElementById('dnPadEmpty');
        if (!pad.canvas || !pad.dirty) {
          if (empty) empty.style.display = 'block';
          return;
        }
        if (empty) empty.style.display = 'none';
        var root = button.closest('[wire\\:id]');
        Livewire.find(root.getAttribute('wire:id')).call('saveSignature', pad.canvas.toDataURL('image/png'));
      }
    };

    window.addEventListener('open-signature-pad', function () {
      pad.canvas = null;
      setTimeout(init, 0);
    });
  })();
</script>
