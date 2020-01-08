<?php

namespace App\Traits;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\Recurso;
use App\Models\Permiso;

trait ValidationDataGenericClass
{
    public function validationDataGeneric()
    {
        if (method_exists($this->route(), 'parameters')) {
            $this->request->add($this->route()->parameters());
            $this->query->add($this->route()->parameters());

            return array_merge($this->route()->parameters(), $this->all());
        }

        return $this->all();
    }

    public function validateResourcePermission()
    {
        $recurso = Recurso::where("id", $this->input('id_recurso'))->first();
        if (!$recurso) {
            throw new HttpResponseException(
                response()->json(['errors' => array("id_recurso" => trans('security.not_found', ['field' => 'id_recurso']))], 404)
            );
        }

        $permiso = Permiso::where("id", $this->input('id_permiso'))->first();
        if (!$permiso) {
            throw new HttpResponseException(
                response()->json(['errors' => array("id_permiso" => trans('security.not_found', ['field' => 'id_permiso']))], 404)
            );
        }
    }

    public function validateUser()
    {
        try {
            $response = $this->client->get($this->url, ['http_errors' => false]);
            $statuscode = $response->getStatusCode();
            $results = $response->getBody();
            $json = json_decode($results);
        } catch (\Throwable $th) {
            throw new HttpResponseException(
                response()->json(['errors' => array("auth_id_usuario" => $th->getMessage() . "catch" . trans('security.error_authentication'))], 403)
            );
        }

        if ($statuscode == 200) {
            if ($json->access === false) {
                throw new HttpResponseException(
                    response()->json(['errors' => array("auth_id_usuario" => trans('security.access_denied'))], 403)
                );
            }
        } else {
            throw new HttpResponseException(
                response()->json(['errors' => array("auth_id_usuario" => trans('security.error_authentication'))], 403)
            );
        }
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();

        throw new HttpResponseException(
            response()->json(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }

    protected function replaceId($id)
    {
        foreach ($this->resourcesRol as $r) {
            $r->id_rol = $id;
            $this->newResources[] = ["id_recurso" => $r->id_recurso, "id_rol" => $id, "id_permiso" => $r->id_permiso, "concedido" => $r->concedido, "desde" => $r->desde, "hasta" => $r->hasta];
        }
    }

    protected function validateNewIp()
    {
        $arrNewIp = explode(".", $this->newOrgCompleted);
        $lastNumberNewIp = $arrNewIp[count($arrNewIp) - 1];
        if ($lastNumberNewIp == "00" || $lastNumberNewIp == "88" || $lastNumberNewIp == "99") {
            throw new HttpResponseException(
                response()->json(['errors' => array("new_org" => trans('security.new_ip_incorrect'))], 400)
            );
        }
        if (!preg_match("/\.[0-9]{2}$/", $this->newOrgCompleted)) {
            throw new HttpResponseException(
                response()->json(['errors' => array("new_org" => trans('security.new_ip_incorrect_format'))], 400)
            );
        }
    }

    protected function completeDigits()
    {
        $this->newOrgCompleted = $this->ip->completeDigits($this->input('new_org'));
    }
}
