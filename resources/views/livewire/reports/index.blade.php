<div>
    <section class="section">
        <div class="container-fluid">
            <div class="row mt-15">
    <div class="col-md-10 col-md-offset-1">
        <div class="panel">
            <div class="panel-heading">
                <div class="panel-title">
                    <h5><strong>Financial Reports</strong></h5>
                </div>
            </div>
            <div class="panel-body">

                <div id="example-tabs">
                    <h3>FINANCIAL STATEMENTS</h3>
                    <section style="height: 500px; overflow: auto">
                        <p>Get a clear picture of how your business is doing. Use these core statements to better understand your financial health</p>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="panel-heading">
                                    <div class="panel-title">
                                        <h5><a href="{{route('reports.income_statement')}}" style="color:blue">Profit & Loss (Income Statement)</a></h5>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    Shows your business’s net profit and summarizes your revenues and expenses in a given time period.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="panel-heading">
                                    <div class="panel-title">
                                        <h5><a href="{{route('reports.balance_sheet')}}" style="color:blue">Balance Sheet</a></h5>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    A snapshot of your finances on a given day. The balance sheet calculates your business’s worth (equity) by subtracting what you owe (liabilities) from what you own (assets).
                                </div>
                            </div>
                        </div>
               
                        <hr>
                        <div class="row">
                            <div class="col-md-6" >
                                <div class="panel-heading">
                                    <div class="panel-title">
                                        <h5><a href="{{route('reports.cashflow')}}" style="color:blue">Cashflow</a></h5>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    Shows how much money is entering and leaving your business. The cash flow statement tells you how much cash you have on hand for a specific time period.
                                </div>
                               
                            </div>
                        </div>
                       
                     
                       
                    </section>
                   
                    <h3>TAXES</h3>
                    <section>
                        <p>A detailed look at the taxes you owe, based on sales taxes collected and paid, and transactions processed.</p>
                        <hr>
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5><a href="#" style="color:blue">Sales Tax Report</a></h5>
                            </div>
                        </div>
                        <div class="panel-body">
                            A breakdown of taxes collected from sales and paid on purchases. You can use this information to prepare sales tax returns and calculate your balance or refund.
                        </div>
                    </section>
                    <h3>CUSTOMERS</h3>
                    <section>
                        <p>Stay on top of overdue invoices and identify high-value or late-paying customers.</p>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="panel-heading">
                                    <div class="panel-title">
                                        <h5><a href="#" style="color:blue">Income by Customer</a></h5>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    A breakdown of paid and unpaid income for every customer.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="panel-heading">
                                    <div class="panel-title">
                                        <h5><a href="#" style="color:blue">Aged Receivables</a></h5>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    Unpaid and overdue invoices for the last 30, 60, and 90+ days.
                                </div>
                            </div>
                        </div>
                        
                    </section>
                    <h3>VENDORS</h3>
                    <section>
                        <p>Track your business spending. See where your money goes, and how much you owe to vendors.</p>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="panel-heading">
                                    <div class="panel-title">
                                        <h5><a href="#" style="color:blue">Purchases By Vendor</a></h5>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    A breakdown of purchases and bills from every vendor.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="panel-heading">
                                    <div class="panel-title">
                                        <h5><a href="#" style="color:blue">Aged Payables</a></h5>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    Unpaid and overdue bills for the last 30, 60, and 90+ days.
                                </div>
                            </div>
                        </div>
                    </section>
                    <h3>DETAILED REPORTS</h3>
                    <section>
                        <p>Dig into the details of your business’s transactions, balances, and accounts.</p>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="panel-heading">
                                    <div class="panel-title">
                                        <h5><a href="#" style="color:blue">Account Balances</a></h5>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    Summary view of balances and activity for all accounts.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="panel-heading">
                                    <div class="panel-title">
                                        <h5><a href="{{ route('reports.trial-balance') }}" style="color:blue">Trial Balance</a></h5>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    The sum of debits and credits for all accounts on a single day. Helps you check for discrepancies to ensure your accounts balance.
                                </div>
                            </div>
                        </div>
               
                        <hr>
                        <div class="col-md-6">
                            <div class="panel-heading">
                                <div class="panel-title">
                                    <h5><a href="#" style="color:blue">Account Transactions(General Ledger)</a></h5>
                                </div>
                            </div>
                            <div class="panel-body">
                                Your complete financial activity: A detailed list of all transactions and totals for every account in your Chart of Accounts.
                            </div>
                           
                        </div>
                     
                    </section>
                </div>

                <!-- /.col-md-12 -->
            </div>
        </div>
        <!-- /.panel -->
    </div>
            </div>
        </div>
    </section>
    {{-- <section class="section">
        <div class="container-fluid">
            <div class="row mt-15">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>FINANCIAL STATEMENTS</h5>
                            </div>
                        </div>
                        <div class="panel-body">
                            Get a clear picture of how your business is doing. Use these core statements to better understand your financial health
                        </div>
                    </div>
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>FINANCIAL STATEMENTS</h5>
                            </div>
                        </div>
                        <div class="panel-body">
                            Get a clear picture of how your business is doing. Use these core statements to better understand your financial health
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section> --}}
</div>
