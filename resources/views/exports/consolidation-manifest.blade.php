<table>
    <tr><td colspan="13"><strong>LCL CARGO MANIFEST - {{ $header['consolidation_number'] }}</strong></td></tr>
    <tr>
        <td><strong>Master Job</strong></td><td>{{ $header['master_job'] }}</td>
        <td><strong>Master B/L</strong></td><td>{{ $header['master_bl'] }}</td>
        <td><strong>Carrier</strong></td><td>{{ $header['carrier'] }}</td>
        <td><strong>Voyage</strong></td><td>{{ $header['voyage'] }}</td>
    </tr>
    <tr>
        <td><strong>Port of Loading</strong></td><td>{{ $header['port_of_loading'] }}</td>
        <td><strong>Port of Discharge</strong></td><td>{{ $header['port_of_discharge'] }}</td>
        <td><strong>ETD</strong></td><td>{{ $header['etd'] }}</td>
        <td><strong>ETA</strong></td><td>{{ $header['eta'] }}</td>
    </tr>
    <tr>
        <td><strong>Origin</strong></td><td>{{ $header['origin'] }}</td>
        <td><strong>Destination</strong></td><td>{{ $header['destination'] }}</td>
        <td><strong>Container(s)</strong></td>
        <td colspan="3">{{ collect($header['containers'])->map(fn ($c) => trim($c['number'] . ' ' . $c['type'] . ($c['seal'] ? ' Seal ' . $c['seal'] : '')))->implode('; ') }}</td>
    </tr>
    <tr><td></td></tr>
    <tr>
        <th><strong>#</strong></th>
        <th><strong>House Job</strong></th>
        <th><strong>HBL No</strong></th>
        <th><strong>Client</strong></th>
        <th><strong>Shipper</strong></th>
        <th><strong>Consignee</strong></th>
        <th><strong>Description of Goods</strong></th>
        <th><strong>HS Code</strong></th>
        <th><strong>Packages</strong></th>
        <th><strong>Gross Wt (kg)</strong></th>
        <th><strong>CBM</strong></th>
        <th><strong>Marks &amp; Nos</strong></th>
        <th><strong>DG</strong></th>
    </tr>
    @foreach ($rows as $row)
        <tr>
            <td>{{ $row['first_line'] ? $row['seq'] : '' }}</td>
            <td>{{ $row['house_job'] }}</td>
            <td>{{ $row['hbl'] }}</td>
            <td>{{ $row['client'] }}</td>
            <td>{{ $row['shipper'] }}</td>
            <td>{{ $row['consignee'] }}</td>
            <td>{{ $row['description'] }}</td>
            <td>{{ $row['hs_code'] }}</td>
            <td>{{ $row['packages'] }}</td>
            <td>{{ $row['gross_weight'] }}</td>
            <td>{{ $row['cbm'] }}</td>
            <td>{{ $row['marks'] }}</td>
            <td>{{ $row['dg'] ? 'Yes' : '' }}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="8"><strong>TOTAL ({{ $totals['clients'] }} clients)</strong></td>
        <td><strong>{{ $totals['packages'] }}</strong></td>
        <td><strong>{{ $totals['gross_weight'] }}</strong></td>
        <td><strong>{{ $totals['cbm'] }}</strong></td>
        <td colspan="2"></td>
    </tr>
</table>
