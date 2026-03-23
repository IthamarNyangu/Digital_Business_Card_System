USE rtc_digital_cards;

-- Create an admin with scripts/create_admin.php after importing the schema.
-- These sample employee records help test the results page locally before CSV import.
INSERT INTO employees (
    employee_number,
    first_name,
    last_name,
    organization,
    title,
    phone,
    email,
    street,
    city,
    region,
    postal_code,
    country,
    mecard_payload,
    qr_code_path
) VALUES
(
    'RTCZ001',
    'Mary',
    'Bwalya',
    'Right to Care Zambia',
    'Program Manager',
    '+260 977 123 100',
    'mary.bwalya@righttocare.org.zm',
    'Plot 12 Addis Ababa Drive',
    'Lusaka',
    'Lusaka Province',
    '10101',
    'Zambia',
    'MECARD:N:Bwalya,Mary;ORG:Right to Care Zambia;TITLE:Program Manager;TEL:+260 977 123 100;EMAIL:mary.bwalya@righttocare.org.zm;ADR:Plot 12 Addis Ababa Drive,Lusaka,Lusaka Province,10101,Zambia;NOTE:Program Manager, Right to Care Zambia;;',
    NULL
),
(
    'RTCZ002',
    'Beatrice',
    'Malama',
    'Right to Care Zambia',
    'Admin Officer',
    '0972448338',
    'beatrice@righttocare-zambia.org',
    'Plot 12 Addis Ababa Drive',
    'Lusaka',
    'Lusaka Province',
    '10101',
    'Zambia',
    'MECARD:N:Malama,Beatrice;ORG:Right to Care Zambia;TITLE:Admin Officer;TEL:0972448338;EMAIL:beatrice@righttocare-zambia.org;ADR:Plot 12 Addis Ababa Drive,Lusaka,Lusaka Province,10101,Zambia;NOTE:Admin Officer, Right to Care Zambia;;',
    NULL
),
(
    'RTCZ003',
    'Ithamar',
    'Nyangu',
    'Right to Care Zambia',
    'Applications Developer',
    '0979511258',
    'ithamar.nyangu@righttocare-zambia.org',
    'Plot 12 Addis Ababa Drive',
    'Lusaka',
    'Lusaka Province',
    '10101',
    'Zambia',
    'MECARD:N:Nyangu,Ithamar;ORG:Right to Care Zambia;TITLE:Applications Developer;TEL:0979511258;EMAIL:ithamar.nyangu@righttocare-zambia.org;ADR:Plot 12 Addis Ababa Drive,Lusaka,Lusaka Province,10101,Zambia;NOTE:Applications Developer, Right to Care Zambia;;',
    NULL
);
