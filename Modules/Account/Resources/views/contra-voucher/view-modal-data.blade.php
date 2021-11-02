<div class="col-md-12">
    <p><b>Voucher No :</b> {{ $voucher[0]->voucher_no }}</p>
</div>
<div class="col-md-12">
    <p><b>Date :</b> {{ date('d-M-Y',strtotime($voucher[0]->voucher_date)) }}</p>
</div>
<div class="col-md-12">
    <p><b>Warehouse :</b> {{ $voucher[0]->warehouse->name }}</p>
</div>
<div class="col-md-12">
    <div class="table-responsive">
        <table class="table table-bordered" id="debit-voucher-table">
            <thead class="bg-primary">
                <th width="40%">Account Name</th>
                <th width="25%" class="text-right">Debit</th>
                <th width="25%" class="text-right">Credit</th>
            </thead>
            <tbody>
                @php
                    $total_debit = 0;
                    $total_credit = 0;
                @endphp     
                @if (!$voucher->isEmpty())
                    @foreach ($voucher as $key => $journal)
                    @php
                        $total_debit += $journal->debit;
                        $total_credit += $journal->credit;
                    @endphp
                    <tr>
                        <td>{{ $journal->coa->name }}</td>
                        <td class="text-right">{{ number_format($journal->debit,2,'.','') }}</td>
                        <td class="text-right">{{ number_format($journal->credit,2,'.','') }}</td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
            <tfoot>
                <tr class="bg-primary">
                    <th class="text-right">Total</th>
                    <th class="text-right">{{ number_format($total_debit,2,'.','') }}</th>
                    <th class="text-right"> {{ number_format($total_credit,2,'.','') }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<div class="col-md-12">
    <p><b>Remarks :</b> {{ $voucher[0]->description }}</p>
</div>