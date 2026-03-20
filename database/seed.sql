USE rtc_digital_cards;

INSERT INTO employees (
    public_token,
    first_name,
    last_name,
    position,
    department,
    phone,
    email,
    location,
    qr_code_path,
    status
) VALUES
('0fd7fba5d96745bda3bc4d4f1fd3171d', 'Mary', 'Bwalya', 'Program Manager', 'Programs', '+260 977 123 100', 'mary.bwalya@righttocare.org.zm', 'Lusaka Office', NULL, 'active'),
('4f2f458bd1b54af58731632c617d4ca0', 'Joseph', 'Tembo', 'Monitoring and Evaluation Officer', 'M&E', '+260 977 123 101', 'joseph.tembo@righttocare.org.zm', 'Ndola Office', NULL, 'active'),
('71b4a2ce938c4c4497e3c19744f50416', 'Ruth', 'Phiri', 'HR Business Partner', 'Human Resources', '+260 977 123 102', 'ruth.phiri@righttocare.org.zm', 'Kitwe Office', NULL, 'active'),
('96b2130f62294d9ca8f5e591b70e4df5', 'Peter', 'Zulu', 'Finance Officer', 'Finance', '+260 977 123 103', 'peter.zulu@righttocare.org.zm', 'Chipata Office', NULL, 'active'),
('c197407d36cf47bd89fe15f7f7b3ab16', 'Agnes', 'Sikazwe', 'Clinical Mentor', 'Clinical Services', '+260 977 123 104', 'agnes.sikazwe@righttocare.org.zm', 'Livingstone Office', NULL, 'inactive');
