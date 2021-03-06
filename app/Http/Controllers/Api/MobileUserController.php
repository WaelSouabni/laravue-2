<?php

namespace App\Http\Controllers\APi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\DB;
use DateTime;
use Illuminate\Support\Facades\Hash;
class MobileUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

        
     /**
     * login api.
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
     // return($request->all());
     if(!empty($request->input('email')) && !empty($request->input('password')) ){
        
      /*  $users = DB::table('users')
                ->where('email', '=', $mail)
                ->get();*/

                ///return($users);
                $res=User::where('email', '=', $request->input('email'))->exists();
                if($res){
                  $users = DB::table('users')
                  ->where('email', '=', $request->input('email'))
                  ->get();
                  if(password_verify($request->input('password'), $users[0]->password )){
                    return response()->json([
                      'success' => true,
                      'user' => $users
                    ]);
                  }
                    
                    else{
                      return response()->json([
                        'success' => false,
                        'message' => 'Invalid Password',
                    ], 401);
                    }

                }
                else{
                  return response()->json([
                    'success' => false,
                    'message' => 'Invalid Email and Password',
                ], 401);
                }

  

      }
 
         
    }
  
    //
     /**
     * Register api.
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
      
        $data = new User;
        $data->name = $request->input('name');
        $data->email = $request->input('email');
        $data->password = bcrypt($request->input('password'));
        $data->save();
        return response()->json([
          'success' => true,
          'user' => $data
      ]);
    }
       /**
     * accompgnateur api.
     *
     * @return \Illuminate\Http\Response
     */
    public function accompagnateur(Request $request)
    {
      
    
      $accompagnateurs = DB::select('select id,nomArabe,prenomArabe,prenomArabe,sexe,telephoneTunisien,telephoneEtranger,image,user_id from accompagnateurs where etat="1"');
        return response()->json([
          'accompagnateur' => $accompagnateurs
      ]);
    }

          /**
     * vol api.
     *
     * @return \Illuminate\Http\Response
     */
    public function vol(Request $request)
    {
      $now = new DateTime();
      $packages = DB::select('select id,labelle,description,NombrePlace,NombrePlaceRestant,prix,dateDepart,image FROM packages where dateDepart> ? and etat="1"',[$now]);
        return response()->json([
          'packages' => $packages
      ]);
    }

           /**
     * volaccom api.
     *
     * @return \Illuminate\Http\Response
     */
    public function volaccom(Request $request)
    {
      $now = new DateTime();
      $packages = DB::select('select id,labelle,description,NombrePlace,NombrePlaceRestant,prix,dateDepart,image,NombreAccRestant FROM packages where dateDepart> ? and etat="1"',[$now]);
        return response()->json([
          'packages' => $packages
      ]);
    }

    
           /**
     * volaccom api.
     *
     * @return \Illuminate\Http\Response
     */
    public function pelerinsPackage($package_id)
    {
     // $now = new DateTime();
      $pelerins = DB::select('select pelerins.id,pelerins.nomArabe, pelerins.prenomArabe,pelerins.etat,pelerins.telephoneTunisien FROM pelerins where pelerins.package_id =?',[$package_id]);
        return response()->json([
          'pelerins' => $pelerins
      ]);
    }
           /**
     * pelerins-accompganteur api.
     *
     * @return \Illuminate\Http\Response
     */
    public function pelerinsAccompgnateurs($id)
    {
     // $now = new DateTime();
      $pelerins = DB::select('select  pelerins.id,pelerins.nomArabe, pelerins.prenomArabe,pelerins.etat,pelerins.telephoneTunisien FROM pelerins where pelerins.createur_id =?',[$id]);
        return response()->json([
          'pelerins' => $pelerins
      ]);
    }
            /**
     * pelerins-accompganteur-vols api.
     *
     * @return \Illuminate\Http\Response
     */
    public function pelerinsAccompgnateursVol($id,$package_id)
    {
     // $now = new DateTime();
      $pelerins = DB::select('select pelerins.id,pelerins.nomArabe, pelerins.prenomArabe,pelerins.etat,pelerins.telephoneTunisien FROM pelerins where pelerins.createur_id =? and pelerins.package_id =?',[$id,$package_id]);
        return response()->json([
          'pelerins' => $pelerins
      ]);
    }

                /**
     * messages-pelerins api.
     *
     * @return \Illuminate\Http\Response
     */
    public function messageHistorique($id)
    {
     // $now = new DateTime();
      $messages = DB::select('select messages.id,messages.description,messages.reponseDescription FROM messages where messages.user_id =?',[$id]);
        return response()->json([
          'messages' => $messages
      ]);
    }
    //
             /**
     * messages-pelerins api.
     *
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request)
    {
     // $now = new DateTime();
     $id=$request->input('user_id');
     $user = User::where('id',  $id)->first();
     $user->password=Hash::make($request->input('password'));
   
      return response()->json(['success' => '???? ??????????????'], 200);
    }

}
