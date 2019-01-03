<?php

if (Database::$type === "mssql") {
	Database::changeColumnType("focus_chat_messages", "message", "NVARCHAR(MAX)");
	Database::changeColumnType("flagged_words", "word", "NVARCHAR(MAX)");
}
