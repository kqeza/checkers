<?php

namespace Prince\Checkers;

class Game
{
    private $board;
    private $currentPlayer = 'w';
    private $bot;

    public function __construct()
    {
        $this->board = new Board();
        $this->bot = new Bot($this->board);
    }
    public function start(): void
    {
        while (true) {
            $this->board->display();
            $this->playerTurn();
            $winner = $this->board->hasWinner();
            if ($winner !== null) {
                echo "Победитель: " . ($winner == 'white' ? 'Вы' : 'Бот') . "!\n";
                break;
            }

            $this->currentPlayer = 'b';
            $this->botTurn();
            $this->currentPlayer = 'w';
            $winner = $this->board->hasWinner();
            if ($winner !== null) {
                $this->board->display();
                echo "Победитель: " . ($winner == 'white' ? 'Вы' : 'Бот') . "!\n";
                break;
            }
        }
    }
    private function playerTurn(): void
    {
        echo "Текущий игрок: " . $this->currentPlayer . "\n";
        while (true) {

            $this->board->display();
            echo "Ваш ход (например, A6 B5, A6 C8 и т.д.): ";
            $move = readline();
            $moves = explode(",", $move);
            $validMoveSequence = true;
            $fromRow = null;
            $fromCol = null;


            foreach ($moves as $singleMove) {
                $singleMove = trim($singleMove);
                $moveParts = explode(" ", $singleMove);

                if (count($moveParts) != 2) {
                    $validMoveSequence = false;
                    break;
                }
                list($from, $to) = $moveParts;


                if (strlen($from) != 2 || strlen($to) != 2) {
                    $validMoveSequence = false;
                    break;
                }

                $toCol = ord(strtoupper($to[0])) - 65;
                $toRow = $this->board->getSize() - (int)$to[1];

                if ($fromRow === null) {
                    $fromCol = ord(strtoupper($from[0])) - 65;
                    $fromRow = $this->board->getSize() - (int)$from[1];
                }

                if (
                    $fromCol < 0 || $fromCol >= $this->board->getSize() ||
                    $fromRow < 0 || $fromRow >= $this->board->getSize() ||
                    $toCol < 0 || $toCol >= $this->board->getSize() ||
                    $toRow < 0 || $toRow >= $this->board->getSize()
                ) {
                    $validMoveSequence = false;
                    break;
                }

                if (!$this->board->makeMove($fromRow, $fromCol, $toRow, $toCol, $this->currentPlayer)) {
                    $validMoveSequence = false;
                    break;
                }
                $fromRow = $toRow;
                $fromCol = $toCol;
            }

            if ($validMoveSequence) {
                break;
            }
            echo "Неверный ход.\n";
        }
    }

    private function botTurn(): void
    {
        if (!$this->bot->makeMove()) {
            echo "У бота нет доступных ходов, вы выиграли!\n";
            exit();
        }
    }
}
