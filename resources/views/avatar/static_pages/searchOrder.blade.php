@extends('avatar.layouts.default')
@section('content')
<section class="avatar-order-shell">
    <div class="avatar-panel avatar-order-panel">
        <div>
            <h1 class="avatar-page-title">{{ __('hyper.searchOrder_title') }}</h1>
            <p class="avatar-page-copy">最多查询近 5 笔订单。</p>
        </div>

        <div class="tab-pane show active" id="bordered-tabs-preview">
            <ul class="nav avatar-segmented mb-3" role="tablist">
                <li class="nav-item">
                    <a href="#dingdanhao" data-toggle="tab" aria-expanded="false" class="nav-link active">
                        <span>{{ __('hyper.searchOrder_order_search_by_number') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#youxiang" data-toggle="tab" aria-expanded="true" class="nav-link">
                        <span>{{ __('hyper.searchOrder_order_search_by_email') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#liulanqi" data-toggle="tab" aria-expanded="false" class="nav-link">
                        <span>{{ __('hyper.searchOrder_order_search_by_ie') }}</span>
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane show active" id="dingdanhao">
                    <form class="needs-validation avatar-form" action="{{ url('search-order-by-sn') }}" method="post">
                        {{ csrf_field() }}
                        <label class="avatar-field">
                            <span class="avatar-label">{{ __('hyper.searchOrder_order_number') }}</span>
                            <input type="text" class="form-control" name="order_sn" required placeholder="{{ __('hyper.searchOrder_input_order_number') }}">
                        </label>
                        <div class="button-row">
                            <button class="avatar-button avatar-button--primary" type="submit">{{ __('hyper.searchOrder_search_now') }}</button>
                            <button type="reset" class="avatar-button avatar-button--secondary">{{ __('hyper.searchOrder_reset_order') }}</button>
                        </div>
                    </form>
                </div>

                <div class="tab-pane" id="youxiang">
                    <form class="needs-validation avatar-form" action="{{ url('search-order-by-email') }}" method="post">
                        {{ csrf_field() }}
                        <label class="avatar-field">
                            <span class="avatar-label">{{ __('hyper.searchOrder_email') }}</span>
                            <input type="email" class="form-control" name="email" required placeholder="{{ __('hyper.searchOrder_input_email') }}">
                        </label>
                        @if(dujiaoka_config_get('is_open_search_pwd', \App\Models\BaseModel::STATUS_CLOSE) == \App\Models\BaseModel::STATUS_OPEN)
                            <label class="avatar-field">
                                <span class="avatar-label">{{ __('hyper.searchOrder_search_password') }}</span>
                                <input type="password" class="form-control" name="search_pwd" required placeholder="{{ __('hyper.searchOrder_input_query_password') }}">
                            </label>
                        @endif
                        <div class="button-row">
                            <button class="avatar-button avatar-button--primary" type="submit">{{ __('hyper.searchOrder_search_now') }}</button>
                            <button type="reset" class="avatar-button avatar-button--secondary">{{ __('hyper.searchOrder_reset_order') }}</button>
                        </div>
                    </form>
                </div>

                <div class="tab-pane" id="liulanqi">
                    <form class="needs-validation avatar-form" action="{{ url('search-order-by-browser') }}" method="post">
                        {{ csrf_field() }}
                        <p class="avatar-page-copy">使用当前浏览器缓存查询最近订单。</p>
                        <button class="avatar-button avatar-button--primary" type="submit">{{ __('hyper.searchOrder_search_now') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@stop
