<?php
    if(empty(SISStudent::getFieldByAlias('hidden_student'))) {
        $custom_field = new CustomField();

        $custom_field
            ->setSourceClass('SISStudent')
            ->setType('checkbox')
            ->setTitle('Hidden Student')
            ->setDescription('Enabling this option will hide this student from all users (including teachers) who do not have the permission to view Hidden Students')
            ->setAlias('hidden_student')
            ->setSystem(1)
            ->persist();
    }