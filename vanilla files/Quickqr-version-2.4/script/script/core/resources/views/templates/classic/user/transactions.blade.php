@extends($activeTheme.'layouts.app')
@section('title', ___('Transactions'))
@section('content')
    <table class="basic-table">
        <thead>
        <tr>
            <th>{{ ___('ID') }}</th>
            <th>{{ ___('Title') }}</th>
            <th>{{ ___('Price') }}</th>
            <th>{{ ___('Payment Method') }}</th>
            <th>{{ ___('Date') }}</th>
            <th>{{ ___('Status') }}</th>
            <th>{{ ___('Invoice') }}</th>
        </tr>
        </thead>
            <tbody>
            @forelse($transactions as $transaction)
                <tr>
                    <td data-label="{{ ___('ID') }}">#{{ $transaction->id }}</td>
                    <td data-label="{{ ___('Title') }}">{{ $transaction->transaction_description }}</td>
                    <td data-label="{{ ___('Price') }}">
                        {{ price_symbol_format($transaction->amount) }}
                    </td>
                    <td data-label="{{ ___('Payment Method') }}">{{ $transaction->transaction_gatway ? ucfirst($transaction->transaction_gatway) : '-' }}</td>
                    <td data-label="{{ ___('Date') }}">{{ date_formating($transaction->created_at) }}</td>
                    <td data-label="{{ ___('Status') }}">
                        @if ($transaction->status == \App\Models\Transaction::STATUS_SUCCESS)
                            @if ($transaction->amount > 0)
                                <span class="dashboard-status-button green">{{ ___('Paid') }}</span>
                            @else
                                <span
                                    class="dashboard-status-button green">{{ ___('Done') }}</span>
                            @endif
                        @elseif($transaction->status == \App\Models\Transaction::STATUS_PENDING)
                            <span
                                class="dashboard-status-button yellow">{{ ___('Pending') }}</span>
                        @else
                            <span
                                class="dashboard-status-button red">{{ ___('Cancelled') }}</span>
                        @endif
                    </td>
                    <td data-label="{{ ___('Invoice') }}">
                        @if ($transaction->status == \App\Models\Transaction::STATUS_SUCCESS && $transaction->amount > 0)
                            <a title="{{ ___('Invoice') }}" data-tippy-placement="top" href="{{route('invoice', $transaction->id)}}" target="_blank"><i class="fa fa-paperclip"></i></a>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">{{ ___('No Data Found') }}</td>
                </tr>
            @endforelse
            </tbody>
    </table>
    {{ $transactions->links($activeTheme.'pagination/default') }}
@endsection
