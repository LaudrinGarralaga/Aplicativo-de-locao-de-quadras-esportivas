<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Reserva;
use App\Horario;
use App\Cliente;
use App\Quadra;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReservaController extends Controller {

    public function index() {

        // Verifica se  está logado
        if (!Auth::check()) {
            return redirect('/');
        }

        // Recupera todos as quadras do banco
        $reservas = Reserva::All();

        return view('listas.reservas_list', compact('reservas'));
    }

    public function create() {

        // Verifica se  está logado
        if (!Auth::check()) {
            return redirect('/');
        }

        // 1: Indica inclusão
        $acao = 1;

        // Obtém os horários, clientes e quadras para exibir no form de cadastro
        $horarios = Horario::orderBy('horario')->get();
        $clientes = Cliente::orderBy('nome')->get();
        $quadras = Quadra::orderBy('tipo')->get();
        // $opcionais = Opcional::orderBy('descricao')->get();

        return view('formularios.reservas_form', compact('acao', 'horarios', 'clientes', 'quadras'));
    }

    public function store(Request $request) {

        $data = $request->data;
        $quadra = $request->quadra_id;
        $horario = $request->horario_id;
        $splitName = explode(' ', $data, 2); 
        $first_name = $splitName[0];
        $last_name = !empty($splitName[1]) ? $splitName[1] : ''; 
        
        $dados2 = DB::table('reservas')
                ->where('data', '=', $last_name) 
                ->where('horario_id', '=', $horario)
                ->where('quadra_id', '=', $quadra)
                ->orWhere(function ($query) use ($quadra, $first_name, $horario) {
                    $query->where('quadra_id', '=', $quadra)
                    ->where('semana', '=', $first_name)
                    ->where('permanente', '=', 'sim')
                    ->where('horario_id', '=', $horario);
                })
            ->count();
                //dd($dados2);
        if ($dados2 >= 1){
            return redirect()->back()
                    ->with('error', 'Reserva não disponível!');
        } else {

            // Recupera todos os dados do formulário
            $reserva = new Reserva;
            $reserva->data = $last_name;
            $reserva->semana = $first_name;
            $reserva->preco = $request->Preco;
            $reserva->horario_id = $request->horario_id;
            $reserva->cliente_id = $request->cliente_id;
            $reserva->quadra_id = $request->quadra_id;
            $reserva->permanente = $request->permanente;
            $reserva->status = $request->status;
            $reserva->user_id = \Illuminate\Support\Facades\Auth::id();
            $reserva->save();

            // Exibe uma mensagem de sucesso se gravou os dados no bando senão exibe uma de erro
            if ($reserva) {
                return redirect()->route('reservas.index')
                                ->with('success', Carbon::parse($reserva->data)->format('d/m/Y') . ' Incluído(a) com sucesso!');
            } else {
                return redirect()->back()->with('error', 'Falha ao cadastrar!');
            }
        }
    }
    public function show($id) {
        //
    }

    public function edit($id) {

        // Verifica se está logado
        if (!Auth::check()) {
            return redirect('/');
        }
        
        // Posiciona no registo a ser alterado
        $reg = Reserva::find($id);

        // 2: Indica Alteração
        $acao = 2;

        // Obtém os horários, clientes e quadras para exibir no form de cadastro
        $horarios = Horario::orderBy('horario')->get();
        $clientes = Cliente::orderBy('nome')->get();
        $quadras = Quadra::orderBy('tipo')->get();
        //$opcionais = Opcional::orderBy('descricao')->get();

        return view('formularios.reservas_form', compact('reg', 'acao', 'clientes', 'quadras', 'horarios'));
    }

    public function update(Request $request, $id) {

        // Posiciona no registo a ser alterado
        $reg = Reserva::find($id);
        //dd($id);

        // Obtém os dados do form
        $data = $request->data;
        $splitName = explode(' ', $data, 2); 
        $first_name = $splitName[0];
        $last_name = !empty($splitName[1]) ? $splitName[1] : ''; 
        $preco = $request->Preco;
        $horario_id = $request->horario_id;
        $cliente_id = $request->cliente_id;
        $quadra_id = $request->quadra_id;
        $permanente = $request->permanente;
        $semana = $request->semana;
        $status = $request->status;
        $user_id = \Illuminate\Support\Facades\Auth::id();
        
        $dados3 = DB::table('reservas')
                ->where('data', '=', $last_name) 
                ->where('horario_id', '=', $horario_id)
                ->where('quadra_id', '=', $quadra_id)
                ->where('status', '=', $status)
                ->orWhere(function ($query) use ($quadra_id, $first_name, $horario_id, $status) {
                    $query->where('quadra_id', '=', $quadra_id)
                    ->where('semana', '=', $first_name)
                    ->where('permanente', '=', 'sim')
                    ->where('horario_id', '=', $horario_id)
                    ->where('status', '=', $status);
                })
            ->count();
        
        if ($dados3 >= 1){
            return redirect()->back()
                    ->with('error', 'Reserva não disponível!');
        } else {

        $dados2 = DB::table('reservas')
            ->where('id', $id)
            ->update(['preco' => $preco, 'semana' => $first_name, 'data' => $last_name, 'horario_id' =>$horario_id, 'cliente_id' => $cliente_id, 'quadra_id' => $quadra_id, 'permanente' => $permanente, 'status' => $status, 'user_id' => $user_id]);
        }
        // Exibe uma mensagem de sucesso se alterou os dados no bando senão exibe uma de erro
        if ($dados2) {
            return redirect()->route('reservas.index')
                            ->with('alter', $request->data . ' Alterado(a) com sucesso!');
        } else {
            return redirect()->back()->with('error', 'Falha ao alterar!');
        }
    }

    public function destroy($id) {
    
        // Posiciona no registo a ser alterado
        $res = Reserva::find($id);

        if ($res->delete()) {
            return redirect()->route('reservas.index')
                            ->with('trash', $res->data . ' Excluído(a) com sucesso!');
        } else {
            return redirect()->back()->with('error', 'Falha ao excluir!');
        }
    }
}