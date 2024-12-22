<?php

namespace Prince\Checkers;

class Board
{
    private $board;
    private $size;

    public function __construct($size = 8)
    {
        $this->size = $size;
        $this->initializeBoard();
    }

    private function initializeBoard()
    {
        $this->board = array_fill(0, $this->size, array_fill(0, $this->size, null));

        for ($row = $this->size - 3; $row < $this->size; $row++) {
            for ($col = 0; $col < $this->size; $col++) {
                if (($row + $col) % 2 != 0) {
                    $this->board[$row][$col] = 'w';
                }
            }
        }

        for ($row = 0; $row < 3; $row++) {
            for ($col = 0; $col < $this->size; $col++) {
                if (($row + $col) % 2 != 0) {
                    $this->board[$row][$col] = 'b';
                }
            }
        }
    }


    public function display()
    {
        echo "  ";
        for ($col = 0; $col < $this->size; $col++) {
            echo chr(65 + $col) . " ";
        }
        echo "\n";
        for ($row = $this->size - 1; $row >= 0; $row--) {
            echo ($this->size - $row) . " ";
            for ($col = 0; $col < $this->size; $col++) {
                echo ($this->board[$row][$col] ?? ".") . " ";
            }
            echo "\n";
        }
        echo "  ";
        for ($col = 0; $col < $this->size; $col++) {
            echo chr(65 + $col) . " ";
        }
        echo "\n";
    }

    public function getPiece($row, $col)
    {
        return $this->board[$row][$col] ?? null;
    }

    public function setPiece($row, $col, $piece)
    {
        $this->board[$row][$col] = $piece;
    }

    public function clearPiece($row, $col)
    {
        $this->board[$row][$col] = null;
    }

    public function getSize()
    {
        return $this->size;
    }

    private function isValidRegularMove($fromRow, $fromCol, $toRow, $toCol, $player): bool
    {
        $piece = $this->getPiece($fromRow, $fromCol);

        if (!$piece || strtolower($piece) !== $player) return false;

        $rowDiff = $toRow - $fromRow;
        $colDiff = abs($toCol - $fromCol);

        if ($colDiff != 1 && $colDiff != 2) return false;
        if (abs($rowDiff) != 1 && abs($rowDiff) != 2) return false;

        if (abs($rowDiff) == 2 && $colDiff == 2) {
            $midRow = $fromRow + $rowDiff / 2;
            $midCol = $fromCol + ($toCol - $fromCol) / 2;
            $midPiece = $this->getPiece($midRow, $midCol);
            if (!$midPiece || strtolower($midPiece) === $player) return false;
            return true;
        }


        if (abs($rowDiff) == 1 && $colDiff == 1) {
            if ($this->getPiece($toRow, $toCol) != null)
                return false;

            return ($player === 'w') ? $rowDiff < 0 : $rowDiff > 0;
        }

        return false;
    }

    private function isValidQueenMove($fromRow, $fromCol, $toRow, $toCol, $player): bool
    {
        $piece = $this->getPiece($fromRow, $fromCol);
        if (!$piece || strtolower($piece) !== strtolower($player)) return false;


        if ($fromRow == $toRow || $fromCol == $toCol)
            return false;

        $rowDir = $toRow > $fromRow ? 1 : -1;
        $colDir = $toCol > $fromCol ? 1 : -1;

        if (abs($fromRow - $toRow) != abs($fromCol - $toCol))
            return false;

        $row = $fromRow + $rowDir;
        $col = $fromCol + $colDir;
        $jumped = false;

        while ($row != $toRow) {
            $piece = $this->getPiece($row, $col);
            if ($piece != null) {
                if ($jumped)
                    return false;
                if (strtolower($piece) == strtolower($player))
                    return false;
                $jumped = true;
            }
            $row += $rowDir;
            $col += $colDir;
        }

        if ($jumped && $this->getPiece($toRow, $toCol) != null) return false;
        if (!$jumped && $this->getPiece($toRow, $toCol) != null) return false;


        return true;
    }

    public function isValidMove($fromRow, $fromCol, $toRow, $toCol, $player): bool
    {
        $piece = $this->getPiece($fromRow, $fromCol);
        if ($piece == null) return false;

        if (strtolower($piece) == 'w' || strtolower($piece) == 'b')
            return $this->isValidRegularMove($fromRow, $fromCol, $toRow, $toCol, $player);
        else
            return $this->isValidQueenMove($fromRow, $fromCol, $toRow, $toCol, $player);
    }


    public function makeMove($fromRow, $fromCol, $toRow, $toCol, $player): bool
    {
        if (!$this->isValidMove($fromRow, $fromCol, $toRow, $toCol, $player)) return false;

        $piece = $this->getPiece($fromRow, $fromCol);
        $this->setPiece($toRow, $toCol, $piece);
        $this->clearPiece($fromRow, $fromCol);

        if (strtolower($piece) == 'w' || strtolower($piece) == 'b') {
            if (abs($toRow - $fromRow) == 2) {
                $midRow = $fromRow + ($toRow - $fromRow) / 2;
                $midCol = $fromCol + ($toCol - $fromCol) / 2;
                $this->clearPiece($midRow, $midCol);
            }
        } else {
            $rowDir = $toRow > $fromRow ? 1 : -1;
            $colDir = $toCol > $fromCol ? 1 : -1;

            $row = $fromRow + $rowDir;
            $col = $fromCol + $colDir;

            while ($row != $toRow) {
                $piece = $this->getPiece($row, $col);
                if ($piece != null) {
                    $this->clearPiece($row, $col);
                    break;
                }
                $row += $rowDir;
                $col += $colDir;
            }
        }

        if ($player === 'w' && $toRow === 0) $piece = 'W';
        else if ($player === 'b' && $toRow === $this->size - 1) $piece = 'B';
        $this->setPiece($toRow, $toCol, $piece);


        return true;
    }

    public function getPossibleMoves($player): array
    {
        $moves = [];
        for ($row = 0; $row < $this->size; $row++) {
            for ($col = 0; $col < $this->size; $col++) {
                $piece = $this->getPiece($row, $col);
                if ($piece == null || strtolower($piece) != $player)
                    continue;

                if (strtolower($piece) === 'w' || strtolower($piece) === 'b') {
                    $pieceMoves = $this->getPossibleRegularMoves($row, $col, $player);
                } else {
                    $pieceMoves = $this->getPossibleQueenMoves($row, $col, $player);
                }
                $moves = array_merge($moves, $pieceMoves);
            }
        }
        return $moves;
    }
    private function getPossibleRegularMoves($fromRow, $fromCol, $player): array
    {
        $possibleMoves = [];
        $moves = [
            [-1, -1],
            [-1, 1],
            [1, -1],
            [1, 1],
            [-2, -2],
            [-2, 2],
            [2, -2],
            [2, 2]
        ];

        foreach ($moves as $move) {
            $toRow = $fromRow + $move[0];
            $toCol = $fromCol + $move[1];
            if ($toRow >= 0 && $toRow < $this->size && $toCol >= 0 && $toCol < $this->size) {
                if ($this->isValidMove($fromRow, $fromCol, $toRow, $toCol, $player)) {
                    $possibleMoves[] = [$fromRow, $fromCol, $toRow, $toCol];
                }
            }
        }

        return $possibleMoves;
    }

    private function getPossibleQueenMoves($fromRow, $fromCol, $player): array
    {
        $possibleMoves = [];

        $directions = [
            [-1, -1],
            [-1, 1],
            [1, -1],
            [1, 1]
        ];

        foreach ($directions as $dir) {
            $toRow = $fromRow + $dir[0];
            $toCol = $fromCol + $dir[1];
            while ($toRow >= 0 && $toRow < $this->size && $toCol >= 0 && $toCol < $this->size) {
                if ($this->isValidMove($fromRow, $fromCol, $toRow, $toCol, $player)) {
                    $possibleMoves[] = [$fromRow, $fromCol, $toRow, $toCol];
                } else {
                    break;
                }
                $toRow += $dir[0];
                $toCol += $dir[1];
            }
        }
        return $possibleMoves;
    }

    public function hasWinner(): ?string
    {
        $whitePieces = 0;
        $blackPieces = 0;

        for ($row = 0; $row < $this->size; $row++) {
            for ($col = 0; $col < $this->size; $col++) {
                $piece = $this->getPiece($row, $col);
                if ($piece === 'w' || $piece === 'W') {
                    $whitePieces++;
                } else if ($piece === 'b' || $piece === 'B') {
                    $blackPieces++;
                }
            }
        }
        if ($whitePieces === 0)
            return 'black';
        if ($blackPieces === 0)
            return 'white';
        return null;
    }
}
