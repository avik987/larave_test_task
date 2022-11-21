<?php

namespace App\Http\Controllers\api\V1\location;

use App\Http\Controllers\Controller;
use App\Http\Requests\LocationRequest;
use App\Models\Location;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Exception;

class LocationController extends Controller
{
    public function createLocation(LocationRequest $request)
    {
        try {
            $location = Location::create($request->all());
            if ($location) {
                return response()->json([
                    'success' => true,
                    'data' => $location,
                ]);
            }
        } catch (Exception $e) {

            throw new HttpResponseException(response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode())->header('Status-Code', $e->getCode()));
        }


    }

    public function updateLocation(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'ip' => ['ip'],
            'user_id' => [ 'integer', 'exists:users,id'],
            'coord_x' => ['regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'],
            'coord_y' => [ 'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/']]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                "errors" => $validator->errors()
            ],)->header('Status-Code', 200);
        }
        try {
            if (isset($request["id"])) {
                $location = Location::query()->find($request["id"]);
                $location->update($request->all());
                $location->save();
                if ($location) {
                    return response()->json([
                        'success' => true,
                        'data' => $location,
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => "Location not found",
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Location id required",
                ]);
            }

        } catch (Exception $e) {

            throw new HttpResponseException(response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode())->header('Status-Code', $e->getCode()));
        }


    }

    public function getLocationsByUserId($user_id)
    {

        $locations = Location::query()->where("user_id", $user_id)->paginate(50);
        return response()->json([
            'success' => true,
            'data' => $locations,
        ]);
    }

    public function getLocations()
    {

        $locations = Location::query()->paginate(50);
        return response()->json([
            'success' => true,
            'data' => $locations,
        ]);
    }

    public function getLocationsByIp(Request $request)
    {
        if (isset($request["ip"])) {
            $locations = Location::query()->where("ip", $request["ip"])->paginate(50);
            return response()->json([
                'success' => true,
                'data' => $locations,
            ]);
        } else {
            return response()->json([
                'success' => true,
                'message' => "Ip id required"]);

        }
    }

    public function deleteLocation($id)
    {
        $location = Location::query()->find($id);
        if ($location) {
            $location->delete();
            return response()->json([
                'success' => true,
                'message' => "Location deleted",
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => "Location not found",
            ]);
        }
    }
}
