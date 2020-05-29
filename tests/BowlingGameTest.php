<?php

namespace PF;

use ArgumentCountError;
use InvalidArgumentException;
use PF\Exceptions\FrameOverflowException;
use PF\Exceptions\TooManyRollsException;
use PF\Exceptions\UnfinishedGameException;
use PF\Exceptions\WrongNumberOfPointsException;

class BowlingGameTest extends \PHPUnit\Framework\TestCase
{
    public function testGetScore_withAllZeros_getZeroScore()
    {
        //set up
        $game = new BowlingGame();

        for ($i = 0; $i < 20; $i++) {
            $game->roll(0);
        }

        //test
        $score = $game->getScore();

        //assert
        self::assertEquals(0, $score);
    }

    public function testGetScore_withAllOnes_get20asScore()
    {
        //set up
        $game = new BowlingGame();

        for ($i = 0; $i < 20; $i++) {
            $game->roll(1);
        }

        //test
        $score = $game->getScore();

        //assert
        self::assertEquals(20, $score);
    }

    public function testGetScore_withASpare_returnScoreWithSpareBonus()
    {
        //set up
        $game = new BowlingGame();

        $game->roll(2);
        $game->roll(8);
        $game->roll(5);
        // 2 + 8 + 5 + (spare bonus) + 17
        for ($i = 0; $i < 17; $i++) {
            $game->roll(1);
        }

        //test
        $score = $game->getScore();

        //assert
        self::assertEquals(37, $score);
    }

    public function testGetScore_withLastSpare_returnScoreWithSpareBonus()
    {
        //set up
        $game = new BowlingGame();

        for ($i = 0; $i < 19; $i++) {
            $game->roll(1);
        }
        // 19 + 9 + 5 (spare bonus)
        $game->roll(9);
        $game->roll(5);

        //test
        $score = $game->getScore();

        //assert
        self::assertEquals(33, $score);
    }

    public function testGetScore_withUnfinishedGame_throwsException()
    {
        $this->expectException(UnfinishedGameException::class);

        //set up
        $game = new BowlingGame();

        for ($i = 0; $i < 19; $i++) {
            $game->roll(1);
        }

        //test
        $score = $game->getScore();
    }

    public function testRoll_withOversizedSpare_throwsException()
    {
        $this->expectException(FrameOverflowException::class);

        //set up
        $game = new BowlingGame();

        //test
        $game->roll(9);
        $game->roll(2);
    }

    public function testGetScore_withAStrike_addsStrikeBonus()
    {
        //set up
        $game = new BowlingGame();

        $game->roll(10);
        $game->roll(5);
        $game->roll(3);
        // 10 + 5 (bonus) + 3 (bonus) + 5 + 3 + 16 = 42
        for ($i = 0; $i < 16; $i++) {
            $game->roll(1);
        }

        //test
        $score = $game->getScore();

        //assert
        self::assertEquals(42, $score);
    }

    public function testGetScore_withAllStrikesIn10thFrame_addsStrikeBonus()
    {
        //set up
        $game = new BowlingGame();

        for ($i = 0; $i < 18; $i++) {
            $game->roll(1);
        }
        // 18 + 10 + 10 (bonus) + 10 (bonus)
        $game->roll(10);
        $game->roll(10);
        $game->roll(10);

        //test
        $score = $game->getScore();

        //assert
        self::assertEquals(48, $score);
    }

    public function testGetScore_withAStrikeIn10thFrame_addsStrikeBonus()
    {
        //set up
        $game = new BowlingGame();

        for ($i = 0; $i < 18; $i++) {
            $game->roll(1);
        }
        // 18 + 10 + 10 (bonus) + 10 (bonus)
        $game->roll(10);
        $game->roll(5);
        $game->roll(6);

        //test
        $score = $game->getScore();

        //assert
        self::assertEquals(39, $score);
    }

    public function testGetScore_withPerfectGame_returns300()
    {
        //set up
        $game = new BowlingGame();

        for ($i = 0; $i < 12; $i++) {
            $game->roll(10);
        }

        //test
        $score = $game->getScore();

        //assert
        self::assertEquals(300, $score);
    }

    public function testRoll_withTooManyRollsAndNoStrikes_throwsException()
    {
        $this->expectException(TooManyRollsException::class);

        //set up
        $game = new BowlingGame();

        //test
        for ($i = 0; $i < 21; $i++) {
            $game->roll(1);
        }
    }

    public function testRoll_withTooManyRollsAndOneStrike_throwsException()
    {
        $this->expectException(TooManyRollsException::class);

        //set up
        $game = new BowlingGame();

        //test
        $game->roll(10);

        for ($i = 0; $i < 19; $i++) {
            $game->roll(1);
        }
    }

    public function testRoll_withTooManyRollsAndPerfectGame_throwsException()
    {
        $this->expectException(TooManyRollsException::class);

        //set up
        $game = new BowlingGame();

        //test
        for ($i = 0; $i < 13; $i++) {
            $game->roll(10);
        }
    }

    public function testRoll_withNegativePoints_throwsException()
    {
        $this->expectException(WrongNumberOfPointsException::class);

        //set up
        $game = new BowlingGame();

        //test
        $game->roll(-1);
    }

    public function testRoll_with11Points_throwsException()
    {
        $this->expectException(WrongNumberOfPointsException::class);

        //set up
        $game = new BowlingGame();

        //test
        $game->roll(11);
    }

    public function testRoll_withFloatingNumberOfPoints_throwsException()
    {
        $this->expectException(InvalidArgumentException::class);

        //set up
        $game = new BowlingGame();

        //test
        $game->roll(5.5);
    }

    public function testRoll_withStringOfPoints_throwsException()
    {
        $this->expectException(InvalidArgumentException::class);

        //set up
        $game = new BowlingGame();

        //test
        $game->roll("5");
    }

    public function testRoll_withNoPoints_throwsException()
    {
        $this->expectException(ArgumentCountError::class);

        //set up
        $game = new BowlingGame();

        //test
        $game->roll();
    }
}