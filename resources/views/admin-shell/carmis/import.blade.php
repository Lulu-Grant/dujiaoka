@extends('admin-shell.layout', ['title' => $title])

@section('content')
    @include('admin-shell.partials.page-header', $header)

    @if(session('status'))
        <div class="panel">
            <div class="panel-body">
                <div class="notice success">{{ session('status') }}</div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="panel">
            <div class="panel-body">
                <div class="notice error">{{ $errors->first() }}</div>
            </div>
        </div>
    @endif

    <div class="panel">
        <div class="panel-body">
            <div class="notice success">
                <strong>批量入口提示：</strong>
                如果你手里有一批卡密，直接粘贴文本最快；如果来源是文件，上传 txt 就行。导入前先确认目标商品，必要时勾选去重。
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-body">
            <form method="post" action="{{ $formAction }}" class="form-stack" enctype="multipart/form-data">
                @csrf

                <div class="filters">
                    <label>
                        <span>关联商品</span>
                        <select name="goods_id" required>
                            <option value="">请选择自动发货商品</option>
                            @foreach($goodsOptions as $value => $label)
                                <option value="{{ $value }}" @if((string) old('goods_id', $defaults['goods_id']) === (string) $value) selected @endif>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span>上传 txt 文件</span>
                        <input type="file" name="carmis_txt" accept=".txt,text/plain">
                    </label>
                    <label>
                        <span><input type="checkbox" name="remove_duplication" value="1" @if(old('remove_duplication', $defaults['remove_duplication'])) checked @endif> 导入前去重</span>
                    </label>
                </div>

                <label>
                    <span>卡密列表</span>
                    <textarea name="carmis_list" rows="14" placeholder="每行一条卡密，支持直接粘贴多行文本">{{ old('carmis_list', $defaults['carmis_list']) }}</textarea>
                </label>

                <div class="meta">
                    优先使用手动粘贴内容；如果未填写卡密列表，则会读取上传的 txt 文件。重复卡密可通过“导入前去重”减少重复入库。
                </div>

                <div class="button-row" style="margin-top: 16px;">
                    <button class="button" type="submit">开始导入卡密</button>
                    <a class="button secondary" href="{{ admin_url('v2/carmis') }}">返回概览</a>
                </div>
            </form>
        </div>
    </div>
@endsection
