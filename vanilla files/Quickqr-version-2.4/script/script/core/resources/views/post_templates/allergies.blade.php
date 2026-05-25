@php
    $data = $allergies->whereIn('id', explode(',', $menu->allergies));
@endphp

@if(count($data))
    <ul class="d-inline-block p-0 padding-left-0 mt-1">
        <li class="d-inline">
            <strong class="menu_excerpt d-inline menu-recipe text-dark">{{ ___('Allergies') }}</strong>
        </li>
        @foreach($data as $allergy)
            <li class='d-inline ml-1 margin-left-5'>
                <img
                    src='{{str($allergy->image)->isUrl() ? $allergy->image : asset('storage/allergies/'.$allergy->image)}}'
                    width='25'
                    data-tippy-placement='top'
                    alt='{{!empty($allergy->translations->{get_lang()}->title) ? $allergy->translations->{get_lang()}->title : $allergy->title}}'
                    title='{{ !empty($allergy->translations->{get_lang()}->title)
                        ? $allergy->translations->{get_lang()}->title
                        : $allergy->title }}'>
            </li>
        @endforeach
    </ul>
@endif
