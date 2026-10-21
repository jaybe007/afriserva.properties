-- Migration 007: Local Government Areas (LGAs) & Location Completeness

-- Populate key Nigerian Local Government Areas (LGAs)
-- Lagos State (state_id = 1)
INSERT INTO local_governments (state_id, name) VALUES
(1, 'Eti-Osa'),
(1, 'Ikeja'),
(1, 'Lagos Island'),
(1, 'Lagos Mainland'),
(1, 'Ibeju-Lekki'),
(1, 'Surulere'),
(1, 'Alimosho'),
(1, 'Kosofe'),
(1, 'Oshodi-Isolo'),
(1, 'Apapa'),
(1, 'Somolu'),
(1, 'Amuwo-Odofin')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Abuja FCT (state_id = 2)
INSERT INTO local_governments (state_id, name) VALUES
(2, 'Abuja Municipal Area Council (AMAC)'),
(2, 'Bwari'),
(2, 'Gwagwalada'),
(2, 'Kuje'),
(2, 'Kwali'),
(2, 'Abaji')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Rivers State (state_id = 3)
INSERT INTO local_governments (state_id, name) VALUES
(3, 'Port Harcourt City'),
(3, 'Obio-Akpor'),
(3, 'Eleme'),
(3, 'Ikwerre'),
(3, 'Oyigbo')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Oyo State (state_id = 4)
INSERT INTO local_governments (state_id, name) VALUES
(4, 'Ibadan North'),
(4, 'Ibadan South-West'),
(4, 'Ibadan North-East'),
(4, 'Oyo East'),
(4, 'Ogbomosho North')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Kano State (state_id = 5)
INSERT INTO local_governments (state_id, name) VALUES
(5, 'Kano Municipal'),
(5, 'Dala'),
(5, 'Fagge'),
(5, 'Nassarawa'),
(5, 'Gwale')
ON DUPLICATE KEY UPDATE name = VALUES(name);
