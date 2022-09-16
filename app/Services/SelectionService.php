<?php

namespace App\Services;

use App\Models\Categories;
use App\Models\Tasks;
use Illuminate\Support\Facades\DB;

class SelectionService
{
    /**
     * Obtaining an already established set of tasks
     * or creating a new one if the set has never been formed or the date of the set is outdated
     *
     * @param string|null $doneList
     * @param string|null $slnList
     * @param string|null $slnDate
     * @return array
     */
    public function getSelectionList(?string $doneList, ?string $slnList, ?string $slnDate): array
    {
        $doneTasks = isset($doneList) ? explode(',', $doneList) : [];

        if (isset($slnList) && isset($slnDate) && $slnDate === date('Y-m-d') . ' 00:00:00') {
            $sln = $this->getCurrentSelection($slnList, $doneTasks);
        } else {
            $sln = $this->setNewSelection($doneTasks);
        }

        return $sln;
    }

    /**
     * Formation of a new selection based on the list of completed tasks
     *
     * @param array $doneList
     * @return array
     */
    public function setNewSelection(array $doneList): array
    {
        $tasks = Tasks::whereNotIn('id', $doneList)->inRandomOrder()->limit(7)->get();

        if (!$tasks->count()) {
            if (empty($doneList)) {
                return [
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'Tasks not found',
                    'data' => []
                ];
            } else {
                return [
                    'status' => 'success',
                    'code' => 204,
                    'message' => 'All tasks has been completed',
                    'data' => []
                ];
            }
        }

        $termsIds = [];
        $data = [];

        foreach ($tasks as $task) {
            array_push($termsIds, $task->category_id);

            $data[$task->id] = [
                'id' => $task->id,
                'status' => 'not completed',
                'title' => $task->title,
                'description' => $task->description,
                'category' => [
                    'id' => $task->category_id,
                    'slug' => '',
                    'title' => ''
                ]
            ];
        }

        $termIds = array_unique($termsIds);

        $terms = Categories::whereIn('id', $termIds)->get();

        if (!$terms->count()) {
            return [
                'status' => 'error',
                'code' => 404,
                'message' => 'Categories not found',
                'data' => []
            ];
        }

        $categories = [];

        foreach ($terms as $term) {
            $categories[$term->id] = [
                'id' => $term->id,
                'slug' => $term->slug,
                'title' => $term->title
            ];
        }

        foreach ($data as $task) {

            $data[$task['id']]['category'] = $categories[$task['category']['id']];

            $data[$task['id']]['id'] = (int)$data[$task['id']]['id'];

            $data[$task['id']]['category']['id'] = (int)$data[$task['id']]['category']['id'];
        }

        sort($data);

        return [
            'status' => 'success',
            'code' => 200,
            'message' => '',
            'data' => [
                'type' => 'new',
                'slnDate' => date('Y-m-d'),
                'slnList' => implode(',', array_column($data, 'id')),
                'list' => array_values($data),
            ]
        ];
    }

    /**
     * Getting the generated selection
     *
     * @param string $slnList
     * @param array $doneList
     * @return array
     */
    public function getCurrentSelection(string $slnList, array $doneList): array
    {
        $tasks = DB::table('tasks as t')
            ->join('categories as c', 'c.id', '=', 't.category_id')
            ->select('t.id', 't.title', 't.description', 'c.id as category_id', 'c.slug as category_slug', 'c.title as category_title')
            ->whereIn('t.id', explode(',', $slnList))
            ->get();

        if (!$tasks->count()) {
            return [
                'status' => 'error',
                'code' => 404,
                'message' => 'Tasks not found',
                'data' => []
            ];
        }

        $data = [];

        foreach ($tasks as $task) {

            $data[] = [
                'id' => (int)$task->id,
                'status' => !empty($doneList) && in_array($task->id, $doneList) ? 'completed' : 'not completed',
                'title' => $task->title,
                'description' => $task->description,
                'category' => [
                    'id' => (int)$task->category_id,
                    'slug' => $task->category_slug,
                    'title' => $task->category_title
                ]
            ];
        }

        return [
            'status' => 'success',
            'code' => 200,
            'message' => '',
            'data' => [
                'type' => 'current',
                'list' => $data
            ]
        ];
    }
}
