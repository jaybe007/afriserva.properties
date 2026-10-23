-- PRODUCTION CREDENTIALS UPDATE (Argon2id Hashed)
-- Generated on: 2026-10-23 10:01:13

-- Super Administrator: Alexander Wright
UPDATE users SET email = 'alexander.wright@afriserva.com', password_hash = '$argon2id$v=19$m=65536,t=4,p=1$TzJVVURpcE4vaVNWRVVEMg$8Q1WICp+S6Gv/g2zXjjToLayXFn60RViZZ0dLfCaqaI' WHERE id = 1;

-- Compliance Moderator: Olufunke Adeyemi
UPDATE users SET email = 'olufunke.adeyemi@afriserva.com', password_hash = '$argon2id$v=19$m=65536,t=4,p=1$TkFldDJrb1luejhQUkRtUw$oyjfryU74RWd6CYUcrwSjSI2lHt42Z14RqJJBuQmXIM' WHERE id = 2;

-- Agency Principal: Chief Babatunde Balogun
UPDATE users SET email = 'babatunde.balogun@prestigeprime.ng', password_hash = '$argon2id$v=19$m=65536,t=4,p=1$YTlNMDd6MjNZUERiUkNxbw$o2cMI+xEH/exZko1ObvFHPF/psiimykWS1rflRAZo+s' WHERE id = 3;

-- Elite Real Estate Agent: Chioma Okonkwo
UPDATE users SET email = 'chioma.okonkwo@prestigeprime.ng', password_hash = '$argon2id$v=19$m=65536,t=4,p=1$Sm5JQkVUVTQvWGd5VlpUTQ$9yNzr70XwcFo3wfHawuB5lppnuzGtyFZGaBFmN54gJs' WHERE id = 4;

-- Abuja Diplomatic Agent: Musa Danjuma
UPDATE users SET email = 'musa.agent@afriserva.com', password_hash = '$argon2id$v=19$m=65536,t=4,p=1$a2J5WmJ0bnouRWhaMU5pbQ$CmIXQERH0645K9bLoLsgt5hvlZ+wh/D4ToepsfLM4Es' WHERE id = 5;

-- Private Landlord: Dr. Emeka Nnamani
UPDATE users SET email = 'dr.emeka.nnamani@afriserva.com', password_hash = '$argon2id$v=19$m=65536,t=4,p=1$bzQwUGM5QkFOS1VmN0tBTA$c4N8YGtJl2T3SgxNeFmIGF+bXIreHVl4nDN6XndlqZM' WHERE id = 6;

-- Client / Investor: Zainab Aliyu
UPDATE users SET email = 'zainab.aliyu@afriserva.com', password_hash = '$argon2id$v=19$m=65536,t=4,p=1$RGJVOXFMVlBtSi9lWGdJSA$iL00lJlgzLasKrQS3Gz5H8n11FLMZ09YpD02oYGZevo' WHERE id = 7;
