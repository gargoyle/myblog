CREATE TABLE `userDetails` (
    `userId` char(64) COLLATE utf8mb4_bin NOT NULL,
    `created` DECIMAL(18,4) NOT NULL,
    `lastUpdated` DECIMAL(18,4) NOT NULL,
    `username` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
    `password` char(255) COLLATE utf8mb4_bin NOT NULL,
    `roles` char(255) COLLATE utf8mb4_bin,
    `emailAddress` char(150) COLLATE utf8mb4_unicode_ci NOT NULL,
    `fullName` char(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `loginToken` char(64) COLLATE utf8mb4_bin NULL,
    PRIMARY KEY (`userId`),
    UNIQUE KEY `EMAIL` (`emailAddress`),
    UNIQUE KEY `UNAME` (`username`),
    UNIQUE KEY `LTOKEN` (`loginToken`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;


CREATE TABLE `events` (
    `eventId` char(64) COLLATE utf8mb4_bin NOT NULL,
    `streamId` char(64) COLLATE utf8mb4_bin NOT NULL,
    `streamSeq` BIGINT UNSIGNED NOT NULL,
    `eventName` char(64) COLLATE utf8mb4_bin NOT NULL,
    `storedAt` DECIMAL(18,4) NOT NULL,
    `eventData` BLOB NOT NULL,
    `metaData` BLOB NOT NULL,
    PRIMARY KEY (`eventId`),
    KEY `stream` (`streamId`, `streamSeq`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE DATABASE my_blog;