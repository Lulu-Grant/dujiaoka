<section class="panel">
    <div class="panel-body table-wrap">
        @if(count($rows))
            <table>
                <thead>
                <tr>
                    @foreach($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @foreach($rows as $row)
                    <tr>
                        @foreach($row as $cell)
                            <td>{!! $cell !!}</td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
            @if(!empty($paginator))
                <div class="pagination">
                    {{ $paginator->links() }}
                </div>
            @endif
        @else
            <div class="empty">
                <div>{{ $empty_title ?? ($empty ?? '当前条件下没有记录。') }}</div>
                @if(!empty($empty_description))
                    <div class="meta" style="margin-top: 8px;">{{ $empty_description }}</div>
                @endif
            </div>
        @endif
    </div>
</section>
