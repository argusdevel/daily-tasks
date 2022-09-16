<?php

namespace App\Http\Controllers\Api\Tasks;

use App\Http\Controllers\Api\Controller;
use App\Services\SelectionService;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\UserNotDefinedException;

class TasksController extends Controller
{
    /**
     * @uses getSelection
     * @uses tickTask
     * @uses changeTask
     */

    /**
     * User object from the Users table
     *
     * @var object
     */
    private object $user;

    /**
     * Getting a selection of tasks for a user
     *
     * @return JsonResponse
     */
    public function getSelection(): JsonResponse
    {

        if (!$this->isAuth()) {
            return response()->json(['status' => 'error', 'message' => 'Access denied', 'data' => []], 401);
        }

        $ss = new SelectionService();

        $sln = $ss->getSelectionList($this->user->done_list, $this->user->sln_list, $this->user->sln_date);

        if ($sln['code'] == 404 || $sln['code'] == 204) {
            return response()->json(['status' => $sln['status'], 'message' => $sln['message'], 'data' => $sln['data']], $sln['code']);
        }

        if ($sln['data']['type'] == 'new') {
            $this->user->sln_date = $sln['data']['slnDate'];
            $this->user->sln_list = $sln['data']['slnList'];

            $this->user->update();
        }

        return response()->json(['status' => $sln['status'], 'message' => $sln['message'], 'data' => $sln['data']['list']], $sln['code']);
    }

    /**
     * Ticking a task as done
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function tickTask(Request $request): JsonResponse
    {

        if (!$this->isAuth()) {
            return response()->json(['status' => 'error', 'message' => 'Access denied', 'data' => []], 401);
        }

        $ts = new TaskService();

        $checkResult = $ts->checkTaskRequestParameters($request, $this->user->id, $this->user->sln_list);

        if ($checkResult['status'] == 'error') {
            return response()->json(['status' => $checkResult['status'], 'message' => $checkResult['message'], 'data' => $checkResult['data']], $checkResult['code']);
        }

        $doneList = isset($this->user->done_list) ? explode(',', $this->user->done_list) : [];

        if (!in_array($checkResult['data']['taskId'], $doneList)) {

            array_push($doneList, $checkResult['data']['taskId']);

            $this->user->done_list = implode(',', $doneList);

            $this->user->update();
        }

        return response()->json(['status' => 'success', 'message' => '', 'data' => ''], 204);
    }

    /**
     * Replacing a task in a user's selection
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function changeTask(Request $request): JsonResponse
    {

        if (!$this->isAuth()) {
            return response()->json(['status' => 'error', 'message' => 'Access denied', 'data' => []], 401);
        }

        $ts = new TaskService();

        $checkResult = $ts->checkTaskRequestParameters($request, $this->user->id, $this->user->sln_list);

        if ($checkResult['status'] == 'error') {
            return response()->json(['status' => $checkResult['status'], 'message' => $checkResult['message'], 'data' => $checkResult['data']], $checkResult['code']);
        }

        $doneList = isset($this->user->done_list) ? explode(',', $this->user->done_list) : [];

        $result = $ts->getAndChangeTask($checkResult['data']['slnList'], $doneList, $checkResult['data']['taskId']);

        if ($result['code'] == 404 || $result['code'] == 204) {
            return response()->json(['status' => $result['status'], 'message' => $result['message'], 'data' => $result['data']], $result['code']);
        }

        $this->user->sln_list = $result['data']['slnList'];

        $this->user->update();

        return response()->json(['status' => 'success', 'message' => '', 'data' => $result['data']['item']], 200);
    }

    /**
     * Authorization check. Returns false if not authorized,
     * otherwise returns true and saves the found user from the Users table in the user object
     *
     * @return bool
     */
    private function isAuth(): bool
    {
        try {
            $this->user = auth()->userOrFail();
        } catch (UserNotDefinedException $e) {
            return false;
        }

        return true;
    }
}
