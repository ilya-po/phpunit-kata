<?php

namespace PF;

use InvalidArgumentException;
use PF\Exceptions\FrameOverflowException;
use PF\Exceptions\TooManyRollsException;
use PF\Exceptions\UnfinishedGameException;
use PF\Exceptions\WrongNumberOfPointsException;

class BowlingGame
{
    public array $rolls = [];
    private int $roll = 1; // of the frame
    private int $frame = 1;
    private int $limit = 20;

    public function roll($points): void
    {
        $this->verifyRoll($points);

        if ($this->isStrikePoints($points) && $this->frame < 10) {
            $this->limit--;
            $this->frame++;
            $this->rolls[] = $points;
            return;
        }

        if ($this->frame === 10) {
            if (($this->isStrikePoints($points) && $this->roll === 1) || $this->isSparePoints($points)) {
                $this->limit++;
            }
        }

        $this->rolls[] = $points;

        if ($this->roll > 1 && $this->frame < 10) {
            $this->frame++;
            $this->roll = 1;
            return;
        }

        $this->roll++;
    }

    public function getScore(): int
    {
        if (count($this->rolls) < $this->limit) {
            throw new UnfinishedGameException("Game is not finished! " . count($this->rolls) . " rolls out of " . $this->limit . " are made.");
        }

        $roll = 0;
        $score = 0;
        for ($frame = 0; $frame < 10; $frame++) {
            if ($this->isStrikeRoll($roll)) {
                $score += $this->getPointsForStrike($roll);
                $roll++;

                continue;
            }

            if ($this->isSpare($roll)) {
                $score += $this->getSpareBonus($roll);
            }

            $score += $this->getNormalScore($roll);
            $roll += 2;
        }

        return $score;
    }

    /**
     * @param $points
     * @throws TooManyRollsException
     * @throws WrongNumberOfPointsException
     * @throws FrameOverflowException
     */
    public function verifyRoll($points): void
    {
        if (!is_int($points)) {
            throw new InvalidArgumentException("Wrong type of points! Expected int, got " . gettype($points));
        }

        if ($this->isWrongNumberOfPoints($points)) {
            throw new WrongNumberOfPointsException("Impossible number of points in a roll: " . $points);
        }

        if ($this->frame < 10 && $this->roll > 1 && $this->getFramePoints($points) > 10) {
            throw new FrameOverflowException("Too many points in the frame! 10 is max, got " . $this->getFramePoints($points));
        }

//        if ($this->frame === 10 && !$this->roll > 2 && $this->getFramePoints($points) > 10) {
//            throw new FrameOverflowException();
//        }

        if ($this->isTooManyRolls()) {
            throw new TooManyRollsException("A roll was made after the game has ended! Rolls: " . count($this->rolls) . " (+1), limit: " . $this->limit);
        }
    }

    /**
     * @param int $points
     * @return bool
     */
    public function isWrongNumberOfPoints(int $points): bool
    {
        return $points < 0 || $points > 10;
    }

    /**
     * @param $points
     * @return mixed
     */
    public function getFramePoints($points): int
    {
        return $this->rolls[count($this->rolls) - 1] + $points;
    }

    /**
     * @param int $points
     * @return bool
     */
    public function isStrikePoints(int $points): bool
    {
        return $points === 10;
    }

    /**
     * @return bool
     */
    public function isTooManyRolls(): bool
    {
        return count($this->rolls) >= $this->limit || $this->roll > 3;
    }

    /**
     * @param int $roll
     * @return bool
     */
    public function isStrikeRoll(int $roll): bool
    {
        return $this->rolls[$roll] === 10;
    }

    /**
     * @param int $roll
     * @return int|mixed
     */
    public function getPointsForStrike(int $roll): int
    {
        return 10 + $this->rolls[$roll + 1] + $this->rolls[$roll + 2];
    }

    /**
     * @param int $roll
     * @return bool
     */
    public function isSpare(int $roll): bool
    {
        return $this->getNormalScore($roll) === 10;
    }

    /**
     * @param int $roll
     * @return mixed
     */
    public function getNormalScore(int $roll): int
    {
        return $this->rolls[$roll] + $this->rolls[$roll + 1];
    }

    /**
     * @param int $roll
     * @return mixed
     */
    public function getSpareBonus(int $roll): int
    {
        return $this->rolls[$roll + 2];
    }

    /**
     * @param $points
     * @return bool
     */
    public function isSparePoints($points): bool
    {
        return $this->roll === 2 && $this->getFramePoints($points) === 10;
    }
}