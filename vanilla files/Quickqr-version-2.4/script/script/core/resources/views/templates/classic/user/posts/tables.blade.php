@extends($activeTheme.'layouts.app')
@section('title', ___('All Tables'))
@section('content')
    <div class="notification notice">{{ ___('Create QR Codes for specific tables.') }}</div>
    <div class="dashboard-box">
        <div class="headline">
            <h3><i class="fas fa-copy"></i> {{ ___('All Tables') }}</h3>
            <a href="javascript:void(0)" class="button ripple-effect add-table"><i
                    class="icon-feather-plus"></i> {{ ___('Add Table') }}</a>
        </div>
        <div class="content with-padding">
            <table class="basic-table dashboard-box-list">
                <thead>
                <tr>
                    <th>{{ ___('Table Number') }}</th>
                    <th>{{ ___('Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($post->tables  as $table)
                    <tr>
                        <td data-label="{{ ___('Table Number') }}">{{ $table->table_no }}</td>
                        <td data-label="{{ ___('Actions') }}">
                            <button onclick="location.href = '{{ route('restaurants.qrbuilder', $post->id) }}?table={{ $table->key }}'" class="button ico green" data-tippy-placement="top" title="{{ ___('QR Code') }}"><i class="far fa-qrcode"></i>
                            </button>
                            <button class="button ico edit-table" data-tippy-placement="top" title="{{ ___('Edit') }}" data-data="{{ str($table)->jsonSerialize() }}"><i class="icon-feather-edit"></i>
                            </button>
                            <form class="d-inline" action="{{ route('restaurants.deleteTable', $post->id) }}" onsubmit="confirm('{{ ___('Are you sure?') }}')" method="post">
                                @csrf
                                <input type="hidden" name="id" value="{{$table->id}}">
                                <button
                                    class="button red ico" title="{{ ___('Delete') }}" data-tippy-placement="top"><i
                                        class="icon-feather-trash-2"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">{{ ___('No Data Found') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Table Popup -->
    <div id="table-dialog" class="zoom-anim-dialog mfp-hide dialog-with-tabs">
        <!--Tabs -->
        <div class="sign-in-form">
            <ul class="popup-tabs-nav">
                <li><a class="modal-title">{{___('Add Table')}}</a></li>
            </ul>

            <div class="popup-tabs-container">
                <!-- Tab -->
                <div class="popup-tab-content">
                    <form action="{{route('restaurants.updateTable', $post->id)}}" method="post">
                        @csrf
                        <div class="submit-field">
                            <input type="number" class="with-border" name="table_number" placeholder="{{ ___('Table Number') }}" required>
                            <input type="hidden" name="id" value="">
                        </div>
                        <!-- Button -->
                        <button class="margin-top-0 button button-sliding-icon ripple-effect"
                                type="submit">{{___('Save')}} <i class="icon-material-outline-arrow-right-alt"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Table Popup / End -->
@endsection

@push('scripts_at_bottom')
    <script>
        let table_modal = $('#table-dialog');
        $('.add-table').on('click', function () {
            table_modal.find('form').trigger('reset');
            table_modal.find('[name="id"]').val('');
            table_modal.find('.modal-title').text(@json(___("Add Table")));

            $.magnificPopup.open({
                items: {
                    src: '#table-dialog',
                    type: 'inline',
                    fixedContentPos: false,
                    fixedBgPos: true,
                    overflowY: 'auto',
                    closeBtnInside: true,
                    preloader: false,
                    midClick: true,
                    removalDelay: 300,
                    mainClass: 'my-mfp-zoom-in'
                }
            });
        });

        $('.edit-table').on('click', function () {
            let data = $(this).data('data');
            table_modal.find('form').trigger('reset');
            table_modal.find('[name="id"]').val(data.id);
            table_modal.find('[name="table_number"]').val(data.table_no);
            table_modal.find('.modal-title').text(@json(___("Edit Table")));

            $.magnificPopup.open({
                items: {
                    src: '#table-dialog',
                    type: 'inline',
                    fixedContentPos: false,
                    fixedBgPos: true,
                    overflowY: 'auto',
                    closeBtnInside: true,
                    preloader: false,
                    midClick: true,
                    removalDelay: 300,
                    mainClass: 'my-mfp-zoom-in'
                }
            });
        });
    </script>
@endpush
