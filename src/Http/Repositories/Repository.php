<?php

namespace Pranjal\Rental\Http\Repositories;


class Repository
{
  // status: true, false
  // message: message to be dispalyed
  // type: validation, notify
  // field: required if type is validation else not required
  protected function responseHelper(bool $status, string $message = "", $data = null)
  {
    return [
      "status" => $status,
      "message" => $message,
      "data" => $data ?? []
    ];
  }

  protected function successResponse(string $message = "", $data = null)
  {
    return [
      "status" => true,
      "message" => $message,
      "data" => $data ?? []
    ];
  }

  protected function errorResponse(string $message = "", $data = null)
  {
    return [
      "status" => false,
      "message" => $message,
      "data" => $data ?? []
    ];
  }
}
