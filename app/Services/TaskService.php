<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TaskService
{
    /**
     * Search for a task based on a selection list and completed tasks list and replace the requested task with the one found
     *
     * @param array $slnList
     * @param array $doneList
     * @param int $taskId
     * @return array
     */
    public function getAndChangeTask(array $slnList, array $doneList, int $taskId): array
    {

        $exclusionIds = array_unique(array_merge($slnList, $doneList));

        $task = DB::table('tasks as t')
            ->join('categories as c', 'c.id', '=', 't.category_id')
            ->select('t.id', 't.title', 't.description', 'c.id as category_id', 'c.slug as category_slug', 'c.title as category_title')
            ->whereNotIn('t.id', $exclusionIds)
            ->inRandomOrder()
            ->first();

        if (!$task) {
            if (empty($doneList)) {
                return [
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'Task not found',
                    'data' => []
                ];
            } else {
                return [
                    'status' => 'success',
                    'code' => 204,
                    'message' => 'No tasks to replace',
                    'data' => []
                ];
            }
        }

        foreach ($slnList as $slnKey => $slnValue) {
            if ($slnValue == $taskId) {
                $slnList[$slnKey] = $task->id;
            }
        }

        return [
            'status' => 'success',
            'code' => 200,
            'message' => '',
            'data' => [
                'slnList' => implode(',', $slnList),
                'item' => [
                    'id' => (int)$task->id,
                    'status' => 'not completed',
                    'title' => $task->title,
                    'description' => $task->description,
                    'category' => [
                        'id' => (int)$task->category_id,
                        'slug' => $task->category_slug,
                        'title' => $task->category_title
                    ]
                ],
            ]
        ];
    }

    /**
     * Checking the user id for a match with an authorized user and
     * checking if the requested task is in the user's selection
     *
     * @param $request
     * @param $userId
     * @param $slnList
     * @return array
     */
    public function checkTaskRequestParameters($request, $userId, $slnList): array
    {

        $validation = Validator::make($request->all(), [
            'userId' => 'required|numeric|min:1',
            'taskId' => 'required|numeric|min:1'
        ]);

        if ($validation->fails()) {
            return [
                'status' => 'error',
                'code' => 400,
                'message' => $validation->messages()->first(),
                'data' => []
            ];
        }

        $data = $request->all();

        if ($userId != $data['userId']) {
            return [
                'status' => 'error',
                'code' => 401,
                'message' => 'Access denied',
                'data' => []
            ];
        }

        $slnList = isset($slnList) ? explode(',', $slnList) : [];

        if (!in_array($data['taskId'], $slnList)) {
            return [
                'status' => 'error',
                'code' => 400,
                'message' => 'Requested task id not in selection list',
                'data' => []
            ];
        }

        return [
            'status' => 'success',
            'code' => 200,
            'message' => '',
            'data' => [
                'taskId' => $data['taskId'],
                'slnList' => $slnList
            ]
        ];
    }
}
