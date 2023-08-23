@php use App\Admin\Controllers\Api\DropDownListLoaderController;use App\Enums\LoaderType; @endphp
@php($listErrorKey = "ageGroups")
@php($sectionId = 'age_group')

<div class="{{$viewClass['form-group']}} {{ $errors->has($listErrorKey) ? 'has-error' : '' }}">

    <div class="{{ $sectionId }}{{$viewClass['field']}}">

        @if($errors->has($listErrorKey))
            @foreach($errors->get($listErrorKey) as $message)
                <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i> {{$message}}</label>
                <br/>
            @endforeach
        @endif

        <table class="table table-hover">
            <thead>
            <tr>
                <td colspan="2">
                    <div class="row">
                        <div class="col-sm-8">
                            {{ \__('admin.models.competition.age_groups.pivot.visible_status') }}
                        </div>
                        <div class="col-sm-4">
                            {{ \__('admin.models.competition.age_groups.title') }}
                        </div>
                    </div>
                </td>
            </tr>
            </thead>
            <tbody class="list-{{$column}}-table">

            @foreach($value ?: [] as $k => $v)
                <?php /* @var $v \App\Models\AgeGroup */?>

                <tr>
                    <td>
                        <div class="form-group">
                            <div class="col-sm-2">
                                <div class="form-check text-right">
                                    <label class="form-check-label">
                                        &nbsp;
                                        <input
                                            class="form-check-input"
                                            type="checkbox" value="1"
                                            name="{{ $column }}[{{ $v->getKey() }}][visible_status]"
                                            {{ $v->_pivot_visible_status ? 'checked="checked"' : '' }}
                                        />
                                        &nbsp;
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-10">
                                <select name="{{ $column }}[{{ $v->getKey() }}][id]" class="form-control" required style="width: 100%">
                                    <option value="{{ $v->getKey() }}" selected>{{ $v->title }}</option>
                                </select>
                            </div>
                        </div>
                    </td>

                    <td style="width: 75px;">
                        <div class="{{$column}}-remove btn btn-warning btn-sm pull-right">
                            <i class="fa fa-trash">&nbsp;</i>{{ __('admin.remove') }}
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <td></td>
                <td>
                    <div class="{{ $column }}-add btn btn-success btn-sm pull-right">
                        <i class="fa fa-save"></i>&nbsp;{{ __('admin.new') }}
                    </div>
                </td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>

<div>
    <template class="{{$column}}-tpl">
        <tr>
            <td>
                <div class="form-group">
                    <div class="col-sm-2">
                        <div class="form-check text-right">
                            <label class="form-check-label">
                                &nbsp;
                                <input class="form-check-input" type="checkbox" value="1" name="{{ $column }}[{id}][visible_status]">
                                &nbsp;
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-10">
                        <select name="{{ $column }}[{id}][id]" class="form-control" required style="width: 100%">
                        </select>
                    </div>
                </div>
            </td>

            <td style="width: 75px;">
                <div class="{{$column}}-remove btn btn-warning btn-sm pull-right">
                    <i class="fa fa-trash">&nbsp;</i>{{ __('admin.remove') }}
                </div>
            </td>
        </tr>
    </template>
</div>

<script>
    (function () {
        document.new_age_groups = 0;

        let initAgeGroup = function () {
            $('input[type="checkbox"]', $(this)).iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%' // optional
            });

            $('select', $(this)).select2({
                ajax: {
                    url: "{{ \route(\admin_get_route(DropDownListLoaderController::ROUTE_NAME), ['type' => LoaderType::AGE_GROUPS->value]) }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term,
                            page: params.page,
                            exclude: $('.{{ $sectionId }} select').map(function () {
                                return $(this).select2('val')
                            }).toArray().join(',')
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.data, function (d) {
                                d.id = d.id;
                                d.text = d.text;
                                return d;
                            }),
                            pagination: {
                                more: data.next_page_url
                            }
                        };
                    },
                    cache: true
                },
                width: '100%',
                allowClear: false,
                placeholder: "{{ \__('admin.models.competition.age_groups.title') }}",
                minimumInputLength: 0,
                escapeMarkup: function (markup) {
                    return markup;
                }
            })
        };

        $('.{{$column}}-add').off('click').on('click', function () {
            let tpl = $('template.{{$column}}-tpl').html();

            tpl = tpl.replaceAll('{id}', 'new_' + (document.new_age_groups++));
            $('tbody.list-{{$column}}-table').append(tpl);
            initAgeGroup.call(
                $('.{{ $sectionId }} tbody tr:last-child')
            )
        });

        $(document).find('.{{ $sectionId }} .{{ $column }}-remove').each(function () {
            initAgeGroup.call($(this).parents('tr'));
        })
    })();
</script>
