<?php

Database::query("UPDATE MASTER_COURSES SET GRAD_SUBJECT_AREA = NULL WHERE GRAD_SUBJECT_AREA = '0'");
Database::query("UPDATE MASTER_COURSES SET GRAD_SUBJECT_AREA2 = NULL WHERE GRAD_SUBJECT_AREA2 = '0'");
Database::query("UPDATE MASTER_COURSES SET GRAD_SUBJECT_AREA3 = NULL WHERE GRAD_SUBJECT_AREA3 = '0'");