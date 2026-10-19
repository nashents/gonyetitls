{{-- Brand block + meta box, as on the trip sheet. Expects $company, $title and $meta (label => value). --}}
<header class="header">
  <div class="brand">
    <img class="brand__logo" src="{{ asset('images/uploads/'.$company->logo) }}" alt="Company Logo" onerror="this.style.display='none'" />
    <div>
      <div class="brand__name">{{ $company->name }}</div>
      <div class="brand__meta">
        <span>{{ $company->street_address }} {{ $company->suburb }} {{ $company->city }} {{ $company->country }}</span><br />
        Tel: <span>{{ $company->phonenumber }}</span>
        @if ($company->second_phonenumber) · {{ $company->second_phonenumber }} @endif
        · Email: <span>{{ $company->email }}</span>
      </div>
    </div>
  </div>

  <aside class="meta">
    <h1>{{ $title }}</h1>
    <dl class="meta-grid">
      @foreach ($meta as $label => $value)
        <dt>{{ $label }}</dt>
        <dd>{!! $value !!}</dd>
      @endforeach
    </dl>
  </aside>
</header>
