-- Convert game DBs to utf8mb4_turkish_ci
SET NAMES utf8mb4;
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

ALTER DATABASE `account` CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;
ALTER DATABASE `common`  CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;
ALTER DATABASE `player`  CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;
ALTER DATABASE `log`     CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;
ALTER DATABASE `DNWeb`   CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;
