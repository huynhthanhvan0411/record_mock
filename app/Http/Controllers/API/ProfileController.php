<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Profile;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\ProfileRequest;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $profiles = Profile::all();
            return response()->json(['suceess'=>true,'data'=> $profiles], Response::HTTP_OK);
        }catch(\Exception $e){
            return response()->json(['suceess'=>false,'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProfileRequest $request)
    {
        DB::beginTransaction();
        try{
            $profile = Profile::create($request->all());
            // public/images
            if($request->hasFile('avatar')){
                $file = $request->file('avatar');
                $file_name = time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('source/img/avatar'), $file_name);
                $profile->avatar = $file_name;
                $profile->save();
                
            }
            DB::commit();
            return response()->json(['sucess'=>true, "Create sucess"=>$profile], Response::HTTP_CREATED);
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try{
            $profile = Profile::
                select(
                    'users.name as Name',
                    'positions.name as Position',
                    'divisions.name as Division',
                    'profiles.phone as Phone',
                    DB::raw('DATE_FORMAT(profiles.birthday, "%d/%m/%Y") as Birthday'),
                    DB::raw('CASE WHEN profiles.gender = 1 THEN "Nam" WHEN profiles.gender = 2 THEN "Nữ" ELSE "Khác" END as Gender'),
                    'profiles.avatar as Avatar',
                    'profiles.address as Address'
                )
                ->leftJoin('users', 'profiles.user_id', '=', 'users.id')
                ->leftJoin('positions', 'profiles.position_id', '=', 'positions.id')
                ->leftJoin('divisions', 'profiles.division_id', '=', 'divisions.id')
                ->findOrFail($id);
            if(is_null($profile)){
                return response()->json(['message' => 'Profile not found'], Response::HTTP_NOT_FOUND);
            }
            return response()->json(['sucess'=>true, ' this is detail user:' => $profile], Response::HTTP_OK);
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();
        try{
            $profile = Profile::find($id);
            if(is_null($profile)){
                return response()->json(['message' => 'Profile not found'], Response::HTTP_NOT_FOUND);
            }
            $profile->update($request->all());
            // public/images
            if($request->hasFile('avatar')){
                $file = $request->file('avatar');
                $file_name = time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images'), $file_name);
                $profile->avatar = $file_name;
                $profile->save();
            }
            DB::commit();
            return response()->json(['sucess'=>true, 'data'=> $profile], Response::HTTP_OK);
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try{
            $profile = Profile::find($id);
            if(is_null($profile)){
                return response()->json(['sucess' => false, 'message' => 'Profile not found'], Response::HTTP_NOT_FOUND);
            }
            $profile->delete();
            DB::commit();
            return response()->json(['sucess'=>true,'message'=> "Delete suceess"], Response::HTTP_OK);
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['sucess' => false, 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function restore(string $id)
    {
        DB::beginTransaction();
        try {
            $profile = Profile::withTrashed()->find($id);
            if (is_null($profile)) {
                return response()->json(['success' => false, 'message' => 'Profile not found'], Response::HTTP_NOT_FOUND);
            }
            $profile->restore();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Restore success'], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
