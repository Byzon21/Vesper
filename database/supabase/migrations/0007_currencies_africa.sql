-- Vesper — Migration 0007 : Référentiel devises — AFRIQUE (ISO 4217)
-- Inclut les devises des petits pays (Loti du Lesotho, Dollar namibien…)
-- et le cas bimétaire critique USD/CDF (RDC).

insert into public.currencies (code, name, symbol, num_code, decimals, region) values
-- Afrique centrale
('XAF', 'Franc CFA (BEAC)',   'FCFA', 950, 0, 'Afrique centrale'),
('CDF', 'Franc congolais',    'FC',   976, 2, 'Afrique centrale'),
('AOA', 'Kwanza angolais',    'Kz',   973, 2, 'Afrique centrale'),
('STN', 'Dobra santoméen',    'Db',   930, 2, 'Afrique centrale'),

-- Afrique de l''Est
('BIF', 'Franc burundais',        'FBu', 108, 0, 'Afrique de l''Est'),
('DJF', 'Franc djiboutien',       'Fdj', 262, 0, 'Afrique de l''Est'),
('ERN', 'Nakfa érythréen',        'Nfk', 232, 2, 'Afrique de l''Est'),
('ETB', 'Birr éthiopien',         'Br',  230, 2, 'Afrique de l''Est'),
('KES', 'Shilling kényan',        'KSh', 404, 2, 'Afrique de l''Est'),
('RWF', 'Franc rwandais',         'RF',  646, 0, 'Afrique de l''Est'),
('SOS', 'Shilling somalien',      'Sh',  706, 2, 'Afrique de l''Est'),
('SSP', 'Livre sud-soudanaise',   'SSP', 728, 2, 'Afrique de l''Est'),
('TZS', 'Shilling tanzanien',     'TSh', 834, 2, 'Afrique de l''Est'),
('UGX', 'Shilling ougandais',     'USh', 800, 0, 'Afrique de l''Est'),

-- Afrique australe
('ZAR', 'Rand sud-africain',    'R',   710, 2, 'Afrique australe'),
('BWP', 'Pula botswanais',      'P',   072, 2, 'Afrique australe'),
('LSL', 'Loti du Lesotho',      'L',   426, 2, 'Afrique australe'),
('NAD', 'Dollar namibien',      'N$',  516, 2, 'Afrique australe'),
('SZL', 'Lilangeni d''Eswatini', 'E',  748, 2, 'Afrique australe'),
('ZMW', 'Kwacha zambien',       'ZK',  967, 2, 'Afrique australe'),
('MWK', 'Kwacha malawite',      'MK',  454, 2, 'Afrique australe'),
('MZN', 'Metical mozambicain',  'MT',  943, 2, 'Afrique australe'),
('MGA', 'Ariary malgache',      'Ar',  969, 2, 'Afrique australe'),
('MUR', 'Roupie mauricienne',   'Rs',  480, 2, 'Afrique australe'),
('SCR', 'Roupie seychelloise',  'SR',  690, 2, 'Afrique australe'),
('KMF', 'Franc comorien',       'CF',  174, 0, 'Afrique australe'),
('ZWG', 'Zimbabwe Gold (ZiG)',  'ZiG', 924, 2, 'Afrique australe'),

-- Afrique de l''Ouest
('XOF', 'Franc CFA (BCEAO)',  'CFA',  952, 0, 'Afrique de l''Ouest'),
('NGN', 'Naira nigérian',     '₦',    566, 2, 'Afrique de l''Ouest'),
('GHS', 'Cedi ghanéen',       'GH₵',  936, 2, 'Afrique de l''Ouest'),
('GMD', 'Dalasi gambien',     'D',    270, 2, 'Afrique de l''Ouest'),
('SLL', 'Leone sierra-léonais','Le',  694, 2, 'Afrique de l''Ouest'),
('LRD', 'Dollar libérien',    'L$',   430, 2, 'Afrique de l''Ouest'),
('CVE', 'Escudo cap-verdien', 'Esc',  132, 2, 'Afrique de l''Ouest'),
('GNF', 'Franc guinéen',      'FG',   324, 0, 'Afrique de l''Ouest'),
('MRU', 'Ouguiya mauritanien','UM',   929, 2, 'Afrique de l''Ouest'),

-- Afrique du Nord
('EGP', 'Livre égyptienne', 'E£', 818, 2, 'Afrique du Nord'),
('DZD', 'Dinar algérien',   'DA', 012, 2, 'Afrique du Nord'),
('MAD', 'Dirham marocain',  'DH', 504, 2, 'Afrique du Nord'),
('TND', 'Dinar tunisien',   'DT', 788, 3, 'Afrique du Nord'),
('LYD', 'Dinar libyen',     'LD', 434, 3, 'Afrique du Nord'),
('SDG', 'Livre soudanaise', 'SDG', 938, 2, 'Afrique du Nord')
on conflict (code) do nothing;
