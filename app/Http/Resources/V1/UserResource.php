<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
         $userData =  parent::toArray($request);
            if(isset($userData["links"])){
                unset($userData["links"]);
            }
            if(isset($userData["path"])){
                unset($userData["path"]);
            }
            if(isset($userData["from"])){
                unset($userData["from"]);
            }
            if(isset($userData["to"])){
                unset($userData["to"]);
            }

        return $userData;
    }
}
