<?php
namespace App\Controllers;

use JSwoole\Controller;
use JSwoole\JSwoole;

class IndexController extends Controller
{
    public function index()
    {
        $data=$this->request->post();
        $a=JSwoole::app()->db->connection('default')->table('user')->select('*')->limit(1)->get();
        // JSwoole::app()->db->connection('default')->insert("INSERT INTO user (name) VALUES ('fdsfa')");
        // $c=\App\Models\UserModel::take(5)->get();
        // $d=new \App\Models\UserModel();
        // $d->openid='fdsfa';
        // $d->save();
        return $this->asJson(['code'=>200, 'data'=>$this->request->get('a')]);
    }
}