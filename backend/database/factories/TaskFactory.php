<?php

namespace Database\Factories;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'folder_id' => $this->faker->numberBetween(1,6), // 固定値、もしくは他のフォルダーIDを動的に生成
            'title' => $this->faker->sentence(3), // ランダムなタイトル
            'status' => $this->faker->numberBetween(1, 3), // ステータスをランダムに生成
            'due_date' => Carbon::now()->addDays($this->faker->numberBetween(1, 30)), // 現在から1〜30日後の期限
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
