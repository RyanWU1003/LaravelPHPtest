<?php

namespace Database\Factories;

use App\Models\member;
use Illuminate\Database\Eloquent\Factories\Factory;

class MemberFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = member::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // $num=rand(1,1000);
        // $num=str_pad($num,4,0,STR_PAD_LEFT);
        return [
            'user_id'=>$this->faker->unique()->randomNumber(4, true),   //unique()唯一的/不重複  numberBetween($min='0001',$max='1000')
            'group_id'=>$this->faker->randomElement(['A','B']),
            'name'=>$this->faker->name(),
        ];
    }
}
