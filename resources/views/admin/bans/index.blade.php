@extends('admin.layouts.admin')

@section('title', trans('admin.bans.title'))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">{{ trans('messages.fields.user') }}</th>
                        <th scope="col">{{ trans('admin.bans.fields.banned-by') }}</th>
                        <th scope="col">{{ trans('admin.bans.fields.reason') }}</th>
                        <th scope="col">{{ trans('messages.fields.date') }}</th>
                        <th scope="col">{{ trans('messages.fields.action') }}</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($bans as $ban)
                        <tr>
                            <th scope="row">{{ $ban->id }}</th>
                            <td @if($ban->trashed()) class="text-strikethrough" @endif><a href="{{ route('admin.users.edit', $ban->user) }}">{{ $ban->user->name }}</a></td>
                            <td><a href="{{ route('admin.users.edit', $ban->author) }}">{{ $ban->author->name }}</a></td>
                            <td @if($ban->trashed()) class="text-strikethrough" @endif>{{ $ban->reason }}</td>
                            <td>{{ format_date_compact($ban->created_at) }}</td>
                            <td>
                                @if(! $ban->trashed())
                                    <a href="{{ route('admin.users.bans.destroy', [$ban->user, $ban]) }}" class="mx-1" title="{{ trans('messages.actions.delete') }}" data-toggle="tooltip" data-confirm="delete"><i class="fas fa-trash"></i></a>
                                @else
                                    <i>{{ trans('admin.bans.removed', ['user' => $ban->remover->name ?? '???', 'date' => format_date_compact($ban->removed_at, true)]) }}</i>
                                @endif
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>

            {{ $bans->links() }}
        </div>
    </div>
@endsection
