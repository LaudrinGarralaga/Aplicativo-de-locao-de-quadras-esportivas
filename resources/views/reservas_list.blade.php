@extends('adminlte::page')

@section('title', 'Lista de Reservas')

@section('content_header')

<div class="col-sm-11">
    <h2> Lista de reservas </h2>
</div>
@stop

@section('content')
<div class='col-sm-1'>
    <a href="{{route('reservas.create')}}" class="btn btn-primary" 
       role="button">Novo</a>
</div>

<div class='col-sm-12'>
    @if (session('status'))
    <div class="alert alert-success">
        {{ session('status') }}
    </div>
    @endif    
    <div class="box">
        <div class="box-header"></div>
        <div class="box-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Horário</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach($reservas as $reserva)
                    <tr>
                        <td>{{$reserva->cliente->nome}}</td>
                        <td>{{$reserva->horario->hora}}</td>
                        <td>{{$reserva->data}}</td>
                        <td>
                            <a href="{{route('reservas.edit', $reserva->id)}}" 
                               class="btn btn-warning" 
                               role="button">Alterar</a> &nbsp;&nbsp;
                            <form style="display: inline-block"
                                  method="post"
                                  action="{{route('reservas.destroy', $reserva->id)}}"
                                  onsubmit="return confirm('Confirma Exclusão?')">
                                {{method_field('delete')}}
                                {{csrf_field()}}
                                <button type="submit"
                                        class="btn btn-danger"> Excluir </button>
                            </form> 
                        </td>
                    </tr>
                    @endforeach        
                </tbody>
            </table>    
            <div class="box-footer"></div>
            {{ $reservas->links() }}
        </div>
    </div>
</div>
@stop
