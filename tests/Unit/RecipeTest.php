<?php

use PHPUnit\Framework\TestCase;

class RecipeTest extends TestCase
{
    private Recipe $recipe;

    protected function setUp(): void
    {
        $this->recipe = new Recipe(
            'Gluten-Free Pancakes',
            'flour, eggs, milk',
            'Mix and cook',
            1,
            4
        );
    }

    public function testRecipeCreation(): void
    {
        $this->assertInstanceOf(Recipe::class, $this->recipe);
        $this->assertEquals('Gluten-Free Pancakes', $this->recipe->getTitle());
        $this->assertEquals('flour, eggs, milk', $this->recipe->getIngredients());
        $this->assertEquals('Mix and cook', $this->recipe->getInstructions());
        $this->assertEquals(1, $this->recipe->getAuthorId());
        $this->assertEquals(4, $this->recipe->getServings());
    }

    public function testSetDescription(): void
    {
        $this->recipe->setDescription('Delicious pancakes');
        $this->assertEquals('Delicious pancakes', $this->recipe->getDescription());
    }

    public function testSetPrepTime(): void
    {
        $this->recipe->setPrepTime(15);
        $this->assertEquals(15, $this->recipe->getPrepTime());
    }

    public function testSetCookTime(): void
    {
        $this->recipe->setCookTime(20);
        $this->assertEquals(20, $this->recipe->getCookTime());
    }

    public function testGetTotalTime(): void
    {
        $this->recipe->setPrepTime(15);
        $this->recipe->setCookTime(20);
        $this->assertEquals(35, $this->recipe->getTotalTime());
    }

    public function testSetDifficulty(): void
    {
        $this->recipe->setDifficulty('easy');
        $this->assertEquals('easy', $this->recipe->getDifficulty());
    }

    public function testSetImageUrl(): void
    {
        $this->recipe->setImageUrl('/images/pancakes.jpg');
        $this->assertEquals('/images/pancakes.jpg', $this->recipe->getImageUrl());
    }

    public function testSetLikesCount(): void
    {
        $this->recipe->setLikesCount(42);
        $this->assertEquals(42, $this->recipe->getLikesCount());
    }

    public function testToArray(): void
    {
        $this->recipe->setDescription('Test description');
        $this->recipe->setPrepTime(10);
        
        $array = $this->recipe->toArray();
        
        $this->assertIsArray($array);
        $this->assertEquals('Gluten-Free Pancakes', $array['title']);
        $this->assertEquals('Test description', $array['description']);
        $this->assertEquals(10, $array['prep_time']);
        $this->assertEquals(4, $array['servings']);
    }

    public function testFromArray(): void
    {
        $data = [
            'id' => 1,
            'title' => 'Test Recipe',
            'ingredients' => 'test ingredients',
            'instructions' => 'test instructions',
            'author_id' => 5,
            'servings' => 2,
            'prep_time' => 10,
            'cook_time' => 20,
            'difficulty' => 'medium',
            'description' => 'Test description'
        ];
        
        $recipe = Recipe::fromArray($data);
        
        $this->assertInstanceOf(Recipe::class, $recipe);
        $this->assertEquals(1, $recipe->getId());
        $this->assertEquals('Test Recipe', $recipe->getTitle());
        $this->assertEquals(10, $recipe->getPrepTime());
        $this->assertEquals(20, $recipe->getCookTime());
        $this->assertEquals('medium', $recipe->getDifficulty());
    }
}