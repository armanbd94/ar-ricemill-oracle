@if (!empty($ledger))
    @foreach ($ledger as $item)
    <tr>
        <td>{{ $item->voucher_date }}</td>
        <td>{{ $item->name }}</td>
        <td>{{ $item->description }}</td>
        <td>{{ $item->voucher_no }}</td>
        <td class="text-right text-success">{{ config('settings.currency_position') == '1' ? config('settings.currency_symbol').number_format($item->debit_amount,2) : number_format($item->debit_amount,2).config('settings.currency_symbol') }}</td>
        <td class="text-right text-danger">{{ config('settings.currency_position') == '1' ? config('settings.currency_symbol').number_format($item->credit_amount,2) : number_format($item->credit_amount,2).config('settings.currency_symbol') }}</td>
        <td class="text-right text-primary">{{ config('settings.currency_position') == '1' ? config('settings.currency_symbol').number_format($item->balance,2) : number_format($item->balance,2).config('settings.currency_symbol') }}</td>
    </tr>
    @endforeach
    <tr>
        <td colspan="4" class="text-right"><b>Grand Total</b></td>
        <td class="text-right text-success"><b>{{ config('settings.currency_position') == '1' ? config('settings.currency_symbol').$total_debit : $total_debit.config('settings.currency_symbol') }}</b></td>
        <td class="text-right text-danger"><b>{{ config('settings.currency_position') == '1' ? config('settings.currency_symbol').$total_credit : $total_credit.config('settings.currency_symbol') }}</b></td>
        <td class="text-right text-primary"><b>{{ config('settings.currency_position') == '1' ? config('settings.currency_symbol').$balance : $balance.config('settings.currency_symbol') }}</b></td>
    </tr>
@else 
<tr><td colspan="7" class="text-center text-danger"><b>No Data Found</b></td></tr>
@endif