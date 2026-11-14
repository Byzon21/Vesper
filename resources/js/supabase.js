/**
 * Vesper — Client Supabase JS (côté client).
 * Utilise la clé PUBLISHABLE (publique) — jamais la clé secrète.
 */
import { createClient } from '@supabase/supabase-js';

const url = import.meta.env.VITE_SUPABASE_URL;
const anonKey = import.meta.env.VITE_SUPABASE_ANON_KEY;

export const supabase = url && anonKey ? createClient(url, anonKey) : null;
