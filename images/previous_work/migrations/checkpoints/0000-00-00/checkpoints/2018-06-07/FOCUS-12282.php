<?php

Database::query("DELETE FROM address_to_district WHERE street IS NULL");