INSERT INTO users (name, email, password, role, active)
VALUES ('Administrador', 'admin@b-loteamentos.local', 'REPLACE_WITH_BCRYPT_HASH', 'admin', 1)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    password = VALUES(password),
    role = VALUES(role),
    active = VALUES(active);
