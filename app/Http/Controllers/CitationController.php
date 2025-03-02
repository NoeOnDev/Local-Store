<?php

namespace App\Http\Controllers;

use App\Repositories\MySQLRepository;
use Illuminate\Http\Request;

class CitationController extends Controller
{
    private $repository;

    public function __construct(MySQLRepository $repository)
    {
        $this->repository = $repository;
    }

    public function createTable(Request $request)
    {
        return response()->json($this->repository->createTable(
            $request->input('name'),
            $request->input('columns')
        ));
    }

    public function addValues($nameTable, Request $request)
    {
        return response()->json($this->repository->addValues(
            $nameTable,
            $request->all()
        ));
    }

    public function getValues($nameTable)
    {
        return response()->json($this->repository->getValues($nameTable));
    }

    public function getByIdValues($nameTable, $id)
    {
        return response()->json($this->repository->getByIdValues($nameTable, $id));
    }

    public function updateValues(Request $request, $nameTable, $id)
    {
        return response()->json($this->repository->updateValues(
            $nameTable,
            $id,
            $request->all()
        ));
    }

    public function deleteValues($nameTable, $id)
    {
        return response()->json(['message' => $this->repository->deleteValues($nameTable, $id)]);
    }

    public function deleteTable($nameTable)
    {
        return response()->json(['message' => $this->repository->deleteTable($nameTable)]);
    }
}
