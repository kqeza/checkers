<?php

namespace Prince\Checkers;

class Bot
{
    private $board;
    private $player = 'b';
    public function __construct(Board $board)
    {
        $this->board = $board;
    }

    public function makeMove()
    {
        $validMoves = $this->getValidMoves();
        if (empty($validMoves)) {
            echo ("У бота Нет доступных ходов");
            return false;
        }
        $move = $validMoves[array_rand($validMoves)];

        return $this->board->makeMove($move[0], $move[1], $move[2], $move[3], $this->player);
    }

    private function getValidMoves()
    {
        $validMoves = [];
        $size = $this->board->getSize();

        for ($row = 0; $row < $size; $row++) {
            for ($col = 0; $col < $size; $col++) {
                if ($this->board->getPiece($row, $col) === $this->player) {
                    for ($toRow = 0; $toRow < $size; $toRow++) {
                        for ($toCol = 0; $toCol < $size; $toCol++) {
                            if ($this->board->isValidMove($row, $col, $toRow, $toCol, $this->player)) {
                                $validMoves[] = [$row, $col, $toRow, $toCol];
                            }
                        }
                    }
                }
            }
        }
        return $validMoves;
    }
}
