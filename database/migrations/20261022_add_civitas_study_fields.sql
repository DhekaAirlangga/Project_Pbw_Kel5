ALTER TABLE users_civitas
    ADD COLUMN jenjang VARCHAR(3) NULL AFTER fakultas,
    ADD COLUMN program_studi VARCHAR(150) NULL AFTER jenjang;
