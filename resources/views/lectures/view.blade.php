<div class="ui container">
    <table class="ui blue striped table">
        <tr>
            <td class="ui top aligned">
                <strong>Тема</strong>
            </td>
            <td class="ui top aligned">
                {{ $lecture->get('name') }}
            </td>
        </tr>
        <tr>
            <td class="ui top aligned">
                <strong>Описание</strong>
            </td>
            <td class="ui top aligned">
                {{ $lecture->get('description') }}
            </td>
        </tr>
    </table>
    <table class="ui blue striped table">
        <tr>
            <td>
                <strong>Документи</strong>
            </td>
        </tr>
        @foreach($lecture->ref('Documents') as $document)
        <tr>
            <td style="width: 50%;">
                {{ $document->get('name') }}
            </td>
            <td style="width: 50%;">
                <a href="{{ $document->getNonCdnUrl() }}" target="_blank">
                    <i class="external square alternate icon"></i>
                </a>
            </td>
        </tr>
        @endforeach
    </table>
</div>