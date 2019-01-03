<?php

Database::query("UPDATE address SET state = UPPER(state), mail_state = UPPER(mail_state)");