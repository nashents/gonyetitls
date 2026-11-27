{{-- System-standard pagination block (same markup as Fuels, Invoices, Purchases ...). Pass $paginator. --}}
@if ($paginator->hasPages())
    <div class="clearfix">
        <nav class="text-center" style="float: right">
            <ul class="pagination rounded-corners">
                {{ $paginator->links() }}
            </ul>
        </nav>
    </div>
@endif
