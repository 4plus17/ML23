<?php
// Moritz-Turnier – Single-file PHP app for Strato webspace
// Features: mobile-first, Live-Tabellen (einklappbar), Torschützenliste (einklappbar),
// HF erst nach Gruppenende, Admin mit Ergebnis-Erfassung, Einzellöschung
// (löscht Ergebnis + Scorer), Torschützen (Nr. 1–12), Scorerliste ohne Namen,
// Schul-Farben konsistent (5er/6er einer Schule = gleiche Farbe)
// Save as index.php
// Admin password: moritz25
// Date: 07.11.

session_start();
header("X-Frame-Options: SAMEORIGIN");

// -----------------------------
// CONFIG
// -----------------------------
$TOURNAMENT_TITLE = "Moritz-Turnier";
$TOURNAMENT_DATE  = "07.11.";
$ADMIN_PASSWORD   = "moritz25"; // change here if needed
$DATA_FILE        = __DIR__ . "/data.json";

// Teams (group key => [team names])
$groups = [
  '5er' => [
    'Mauritius 5er',
    'Liebfrauen 5er',
    'Gesamtschule Büren 5er',
    'Realschule Lichtenau 5er',
    'Profilschule Fürstenberg 5er',
  ],
  '6er' => [
    'Mauritius 6er',
    'Liebfrauen 6er',
    'Gesamtschule Büren 6er',
    'Realschule Lichtenau 6er',
    'Profilschule Fürstenberg 6er',
    'Sekundarschule Borchen 6er',
  ],
];

// Schedule (based on your screenshot; adjust freely). id must be unique.
$schedule = [
  ['id'=>1,  'slot'=>1,  'start'=>'08:30', 'end'=>'08:38', 'phase'=>'Gruppenphase 5er', 't1'=>'Mauritius 5er',            't2'=>'Liebfrauen 5er'],
  ['id'=>2,  'slot'=>2,  'start'=>'08:40', 'end'=>'08:48', 'phase'=>'Gruppenphase 6er', 't1'=>'Mauritius 6er',             't2'=>'Liebfrauen 6er'],
  ['id'=>3,  'slot'=>3,  'start'=>'08:50', 'end'=>'09:58', 'phase'=>'Gruppenphase 5er', 't1'=>'Liebfrauen 5er',            't2'=>'Gesamtschule Büren 5er'],
  ['id'=>4,  'slot'=>4,  'start'=>'09:00', 'end'=>'09:08', 'phase'=>'Gruppenphase 6er', 't1'=>'Liebfrauen 6er',            't2'=>'Gesamtschule Büren 6er'],
  ['id'=>5,  'slot'=>5,  'start'=>'09:10', 'end'=>'09:18', 'phase'=>'Gruppenphase 5er', 't1'=>'Profilschule Fürstenberg 5er','t2'=>'Realschule Lichtenau 5er'],
  ['id'=>6,  'slot'=>6,  'start'=>'09:20', 'end'=>'09:28', 'phase'=>'Gruppenphase 6er', 't1'=>'Sekundarschule Borchen 6er', 't2'=>'Realschule Lichtenau 6er'],
  ['id'=>7,  'slot'=>7,  'start'=>'09:30', 'end'=>'09:38', 'phase'=>'Gruppenphase 5er', 't1'=>'Mauritius 5er',             't2'=>'Profilschule Fürstenberg 5er'],
  ['id'=>8,  'slot'=>8,  'start'=>'09:40', 'end'=>'09:48', 'phase'=>'Gruppenphase 6er', 't1'=>'Mauritius 6er',              't2'=>'Profilschule Fürstenberg 6er'],
  ['id'=>9,  'slot'=>9,  'start'=>'09:50', 'end'=>'10:58','phase'=>'Gruppenphase 5er', 't1'=>'Liebfrauen 5er',             't2'=>'Realschule Lichtenau 5er'],
  ['id'=>10, 'slot'=>10, 'start'=>'10:00', 'end'=>'10:08','phase'=>'Gruppenphase 6er', 't1'=>'Liebfrauen 6er',              't2'=>'Realschule Lichtenau 6er'],
  ['id'=>11, 'slot'=>11, 'start'=>'10:10', 'end'=>'10:19','phase'=>'Gruppenphase 5er', 't1'=>'Gesamtschule Büren 5er',     't2'=>'Mauritius 5er'],
  ['id'=>12, 'slot'=>12, 'start'=>'10:20', 'end'=>'10:28','phase'=>'Gruppenphase 6er', 't1'=>'Gesamtschule Büren 6er',      't2'=>'Sekundarschule Borchen 6er'],
  ['id'=>13, 'slot'=>13, 'start'=>'10:30', 'end'=>'10:38','phase'=>'Gruppenphase 5er', 't1'=>'Mauritius 5er',              't2'=>'Realschule Lichtenau 5er'],
  ['id'=>14, 'slot'=>14, 'start'=>'10:40', 'end'=>'10:48','phase'=>'Gruppenphase 6er', 't1'=>'Mauritius 6er',               't2'=>'Realschule Lichtenau 6er'],
  ['id'=>15, 'slot'=>15, 'start'=>'10:50', 'end'=>'11:58','phase'=>'Gruppenphase 5er', 't1'=>'Profilschule Fürstenberg 5er','t2'=>'Sekundarschule Borchen 6er'],
  ['id'=>16, 'slot'=>16, 'start'=>'11:00', 'end'=>'11:08','phase'=>'Gruppenphase 5er', 't1'=>'Liebfrauen 5er',             't2'=>'Profilschule Fürstenberg 5er'],
  ['id'=>17, 'slot'=>17, 'start'=>'11:10', 'end'=>'11:18','phase'=>'Gruppenphase 5er', 't1'=>'Gesamtschule Büren 5er',      't2'=>'Realschule Lichtenau 5er'],
  ['id'=>18, 'slot'=>18, 'start'=>'11:20', 'end'=>'11:28','phase'=>'Gruppenphase 6er', 't1'=>'Gesamtschule Büren 6er',      't2'=>'Profilschule Fürstenberg 6er'],
  ['id'=>19, 'slot'=>19, 'start'=>'11:30', 'end'=>'11:38','phase'=>'Gruppenphase 5er', 't1'=>'Gesamtschule Büren 5er',      't2'=>'Profilschule Fürstenberg 5er'],
  ['id'=>20, 'slot'=>20, 'start'=>'11:40', 'end'=>'11:48','phase'=>'Halbfinale 1',     't1'=>'1. 5er',                      't2'=>'2. 6er'],
  ['id'=>21, 'slot'=>21, 'start'=>'11:50', 'end'=>'12:58','phase'=>'Halbfinale 2',     't1'=>'1. 6er',                      't2'=>'2. 5er'],
  ['id'=>22, 'slot'=>22, 'start'=>'12:00', 'end'=>'12:08','phase'=>'Finale',           't1'=>'Sieger HF1',                  't2'=>'Sieger HF2'],
];

// -----------------------------
// STORAGE
// -----------------------------
function load_state($file) {
  if (!file_exists($file)) {
    $state = [ 'results' => [], 'scorers' => [], 'notes' => '' ];
    file_put_contents($file, json_encode($state, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    return $state;
  }
  $json = file_get_contents($file);
  $state = json_decode($json, true);
  if (!$state) $state = ['results'=>[], 'scorers'=>[], 'notes'=>''];
  if (!isset($state['results'])) $state['results'] = [];
  if (!isset($state['scorers'])) $state['scorers'] = [];
  if (!isset($state['notes'])) $state['notes'] = '';
  return $state;
}
function save_state($file, $state) { file_put_contents($file, json_encode($state, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); }
$state = load_state($DATA_FILE);

// -----------------------------
// HELPERS
// -----------------------------
function h($s){return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');}
// Schul-Farben (5er/6er gleicher Hue)
function school_of($team){ return preg_replace('/\s+[56]er$/','', $team); }
function pastel_color_for($name) {
  $base = school_of($name);
  $h = abs(crc32($base)) % 360; $s = 55; $l = 88;
  return "hsl($h {$s}% {$l}%)";
}
function match_group($m) {
  if (str_starts_with($m['phase'], 'Gruppenphase 5')) return '5er';
  if (str_starts_with($m['phase'], 'Gruppenphase 6')) return '6er';
  return null;
}
function compute_table($schedule, $state, $group_key) {
  global $groups; $table = [];
  foreach ($groups[$group_key] as $t) { $table[$t] = ['team'=>$t,'gp'=>0,'w'=>0,'d'=>0,'l'=>0,'gf'=>0,'ga'=>0,'gd'=>0,'pts'=>0]; }
  foreach ($schedule as $m) {
    $g = match_group($m); if ($g !== $group_key) continue; $id = $m['id'];
    if (!isset($state['results'][$id])) continue; $r = $state['results'][$id];
    if ($r['g1'] === '' || $r['g2'] === '') continue;
    $t1=$m['t1']; $t2=$m['t2']; if (!isset($table[$t1])||!isset($table[$t2])) continue;
    $g1=(int)$r['g1']; $g2=(int)$r['g2'];
    $table[$t1]['gp']++; $table[$t2]['gp']++;
    $table[$t1]['gf'] += $g1; $table[$t1]['ga'] += $g2; $table[$t2]['gf'] += $g2; $table[$t2]['ga'] += $g1;
    $table[$t1]['gd'] = $table[$t1]['gf'] - $table[$t1]['ga']; $table[$t2]['gd'] = $table[$t2]['gf'] - $table[$t2]['ga'];
    if ($g1>$g2){$table[$t1]['w']++;$table[$t2]['l']++;$table[$t1]['pts']+=3;} elseif ($g1<$g2){$table[$t2]['w']++;$table[$t1]['l']++;$table[$t2]['pts']+=3;} else {$table[$t1]['d']++;$table[$t2]['d']++;$table[$t1]['pts']+=1;$table[$t2]['pts']+=1;}
  }
  usort($table,function($a,$b){ if($a['pts']!==$b['pts']) return $b['pts']-$a['pts']; if($a['gd']!==$b['gd']) return $b['gd']-$a['gd']; if($a['gf']!==$b['gf']) return $b['gf']-$a['gf']; return strcmp($a['team'],$b['team']); });
  return $table;
}
function group_phase_completed($schedule, $state, $group_key){
  $total = 0; $done = 0;
  foreach ($schedule as $m){
    $g = match_group($m); if ($g !== $group_key) continue;
    if (!str_starts_with($m['phase'], 'Gruppenphase')) continue;
    $total++;
    $id = $m['id'];
    if (isset($state['results'][$id])){
      $r = $state['results'][$id];
      if ($r['g1'] !== '' && $r['g2'] !== '') $done++;
    }
  }
  return $total>0 && $done === $total;
}
function compute_scorers($sched, $state){
  // Aggregate scorers across matches -> [team, num, goals]
  $byId = [];
  foreach ($sched as $m) { $byId[$m['id']] = $m; }
  $agg = [];
  $sc = $state['scorers'] ?? [];
  foreach ($sc as $mid => $dat) {
    if (!isset($byId[$mid])) continue; $mm = $byId[$mid];
    foreach (['t1','t2'] as $side) {
      $team = ($side==='t1') ? $mm['t1'] : $mm['t2'];
      if (!isset($dat[$side]) || !is_array($dat[$side])) continue;
      foreach ($dat[$side] as $num=>$cnt) {
        $cnt = (int)$cnt; if ($cnt<=0) continue;
        $key = $team.'#'.$num;
        if (!isset($agg[$key])) $agg[$key] = ['team'=>$team,'num'=>(int)$num,'goals'=>0];
        $agg[$key]['goals'] += $cnt;
      }
    }
  }
  $rows = array_values($agg);
  usort($rows, function($a,$b){ if($a['goals']!==$b['goals']) return $b['goals']-$a['goals']; if($a['team']!==$b['team']) return strcmp($a['team'],$b['team']); return $a['num']-$b['num']; });
  return $rows;
}
function auto_fill_knockouts(&$schedule, $tables, $completed) {
  foreach ($schedule as &$m) {
    if ($m['phase'] === 'Halbfinale 1') {
      if (!empty($completed['5er']) && !empty($completed['6er'])){
        $m['t1'] = $tables['5er'][0]['team'] ?? $m['t1'];
        $m['t2'] = $tables['6er'][1]['team'] ?? $m['t2'];
      }
    } elseif ($m['phase'] === 'Halbfinale 2') {
      if (!empty($completed['5er']) && !empty($completed['6er'])){
        $m['t1'] = $tables['6er'][0]['team'] ?? $m['t1'];
        $m['t2'] = $tables['5er'][1]['team'] ?? $m['t2'];
      }
    } elseif ($m['phase'] === 'Finale') {
      $hf1 = null; $hf2 = null;
      foreach ($schedule as $mm) {
        if ($mm['phase']==='Halbfinale 1' && isset($GLOBALS['state']['results'][$mm['id']])) {
          $r=$GLOBALS['state']['results'][$mm['id']];
          if($r['g1']!==''&&$r['g2']!=='') $hf1 = ($r['g1']>$r['g2'])? $mm['t1'] : (($r['g2']>$r['g1'])? $mm['t2'] : null);
        }
        if ($mm['phase']==='Halbfinale 2' && isset($GLOBALS['state']['results'][$mm['id']])) {
          $r=$GLOBALS['state']['results'][$mm['id']];
          if($r['g1']!==''&&$r['g2']!=='') $hf2 = ($r['g1']>$r['g2'])? $mm['t1'] : (($r['g2']>$r['g1'])? $mm['t2'] : null);
        }
      }
      if ($hf1) $m['t1'] = $hf1; if ($hf2) $m['t2'] = $hf2;
    }
  }
}

// -----------------------------
// API ACTIONS
// -----------------------------
$action = $_GET['action'] ?? '';
if ($action === 'state') {
  $tables = [ '5er' => compute_table($schedule, $state, '5er'), '6er' => compute_table($schedule, $state, '6er') ];
  $completed = [ '5er' => group_phase_completed($schedule,$state,'5er'), '6er' => group_phase_completed($schedule,$state,'6er') ];
  $sched_copy = $schedule; auto_fill_knockouts($sched_copy, $tables, $completed);
  $scorers = compute_scorers($sched_copy, $state);

  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['schedule'=>$sched_copy,'results'=>$state['results'],'tables'=>$tables,'scorers'=>$scorers], JSON_UNESCAPED_UNICODE); exit;
}
if ($action === 'login' && isset($_POST['pw'])) {
  if (hash_equals($ADMIN_PASSWORD, $_POST['pw'])) { $_SESSION['admin'] = true; header('Location: index.php?admin=1'); exit; }
  $_SESSION['admin'] = false; header('Location: index.php?admin=1&err=1'); exit;
}
if ($action === 'save' && !empty($_SESSION['admin'])) {
  $results = $state['results'];
  $scorers = $state['scorers'] ?? [];

  // 0) Einzel-Löschen per Button -> Ergebnis UND Torschützen entfernen
  if (isset($_POST['del'])) {
    $delId = (int)$_POST['del'];
    unset($results[$delId]);
    unset($scorers[$delId]);
    $state['results'] = $results;
    $state['scorers'] = $scorers;
    save_state($DATA_FILE, $state);
    header('Location: index.php?admin=1&deleted='.$delId); exit;
  }

  // 1) Standard-Speichern über alle Spiele (Ergebnisse + Scorer)
  foreach ($schedule as $m) {
    $id = $m['id'];

    // Ergebnisse
    $g1_raw = $_POST['g1_'.$id] ?? null;
    $g2_raw = $_POST['g2_'.$id] ?? null;
    if (!($g1_raw === null && $g2_raw === null)) {
      if ($g1_raw === '' && $g2_raw === '') { unset($results[$id]); }
      else {
        $g1 = ($g1_raw === '' ? '' : (int)$g1_raw);
        $g2 = ($g2_raw === '' ? '' : (int)$g2_raw);
        $results[$id] = ['g1'=>$g1, 'g2'=>$g2];
      }
    }

    // Torschützen (nur wenn für dieses Match Felder im POST vorhanden sind)
    $present = false;
    $t1 = []; $t2 = [];
    for ($n=1; $n<=12; $n++){
      $k1 = 'sc1_'.$id.'_'.$n; $k2 = 'sc2_'.$id.'_'.$n;
      if (array_key_exists($k1, $_POST)) { $present = true; $v = trim((string)$_POST[$k1]); if ($v !== '' && (int)$v > 0) $t1[$n] = (int)$v; }
      if (array_key_exists($k2, $_POST)) { $present = true; $v = trim((string)$_POST[$k2]); if ($v !== '' && (int)$v > 0) $t2[$n] = (int)$v; }
    }
    if ($present) {
      if (empty($t1) && empty($t2)) unset($scorers[$id]);
      else $scorers[$id] = ['t1'=>$t1, 't2'=>$t2];
    }
  }

  $state['results'] = $results;
  $state['scorers'] = $scorers;
  save_state($DATA_FILE, $state);
  header('Location: index.php?admin=1&saved=1'); exit;
}
if ($action === 'logout') { session_destroy(); header('Location: index.php'); exit; }

// -----------------------------
// VIEW DATA
// -----------------------------
$tables = [ '5er' => compute_table($schedule, $state, '5er'), '6er' => compute_table($schedule, $state, '6er') ];
$completed = [ '5er' => group_phase_completed($schedule,$state,'5er'), '6er' => group_phase_completed($schedule,$state,'6er') ];
$sched_copy = $schedule; auto_fill_knockouts($sched_copy, $tables, $completed);

?><!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?=h($TOURNAMENT_TITLE)?> – Live</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
  <style>
    :root{ --bg:#0b1020; --card:#0f172a; --muted:#cbd5e1; --accent:#22c55e; --pill:#1f2937; }
    *{ box-sizing:border-box; }
    html,body{ height:100%; }
    body{ overflow-x:hidden; margin:0; font-family:Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; background:var(--bg); color:#e5e7eb; }
    header{ position:sticky; top:0; z-index:20; backdrop-filter:saturate(1.2) blur(8px); background:rgba(11,16,32,.72); border-bottom:1px solid rgba(255,255,255,.06); }
    .wrap{ max-width:1100px; margin:0 auto; padding:12px 14px; }
    h1{ margin:0; font-size: clamp(20px, 3.5vw, 36px); letter-spacing:.2px; }
    .sub{ color:#9ca3af; font-size:14px; }
    .grid{ display:grid; gap:12px; grid-template-columns:1fr; }
    @media (min-width:900px){ .grid{ grid-template-columns: 2fr 1fr; } }
    .card{ background:var(--card); border:1px solid rgba(255,255,255,.08); border-radius:14px; padding:14px; }
    .pill{ font-size:12px; background:var(--pill); color:#e5e7eb; padding:4px 8px; border-radius:999px; }
    .muted{ color:#9ca3af; }

    /* Collapsible cards (used by standings + scorers) */
    details.card{ padding:0; overflow:hidden; }
    details.card > summary{ list-style:none; cursor:pointer; display:flex; align-items:center; justify-content:space-between; gap:10px; padding:14px; background:var(--card); border-bottom:1px solid rgba(255,255,255,.08); }
    details.card > summary::-webkit-details-marker{ display:none; }
    .caret{ transition:transform .2s ease; }
    details[open] .caret{ transform:rotate(180deg); }
    .standings-wrap, .scorers-public-wrap{ padding:14px; }

    /* Classic standings table look */
    .standings-table{ width:100%; border-collapse:separate; border-spacing:0; background:rgba(255,255,255,.02); border:1px solid rgba(255,255,255,.08); border-radius:12px; overflow:hidden; }
    .standings-table thead th{ background:rgba(255,255,255,.04); font-weight:700; font-size:13px; padding:10px 8px; text-align:center; }
    .standings-table tbody td{ padding:10px 8px; border-top:1px solid rgba(255,255,255,.06); text-align:center; }
    .standings-table tbody tr:nth-child(even){ background:rgba(255,255,255,.02); }
    .standings-table .col-team{ text-align:left; }
    .badge-rank{ display:inline-block; min-width:24px; text-align:center; padding:2px 6px; border-radius:999px; background:var(--pill); font-size:12px; margin-right:6px; }

    /* Mobile compact rows inside collapsible */
    .standings-mobile h3{ margin:0 0 6px 0; }
    .standings-mobile .row{ display:flex; justify-content:space-between; gap:8px; padding:6px 0; border-bottom:1px dashed rgba(255,255,255,.08); }
    .standings-mobile .left{ display:flex; gap:6px; align-items:center; }
    .standings-mobile .meta{ color:#cbd5e1; }

    /* Scorer list */
    .scorer-list{ display:block; }
    .scorer-row{ display:flex; justify-content:space-between; align-items:center; gap:8px; padding:6px 0; border-bottom:1px dashed rgba(255,255,255,.08); }
    .scorer-row .left{ display:flex; gap:6px; align-items:center; }

    /* Desktop schedule table */
    table{ width:100%; border-collapse:collapse; }
    th,td{ padding:10px 8px; text-align:left; border-bottom:1px dashed rgba(255,255,255,.08); }
    th{ color:#cbd5e1; font-weight:600; font-size:13px; }
    .slot{ color:#cbd5e1; font-weight:600; width:44px; }
    .time{ white-space:nowrap; width:84px; }
    .score{ font-weight:800; text-align:center; width:44px; }
    .teamchip{ --c: #eef; background: var(--c); color:#0b1020; font-weight:600; padding:6px 10px; border-radius:10px; display:inline-block; min-width: 40%; text-wrap:balance; }

    .floating-refresh{ position:fixed; right:14px; bottom:14px; z-index:30; appearance:none; border:0; border-radius:999px; padding:12px 14px; font-weight:800; cursor:pointer; background:#22c55e; color:#0b1020; box-shadow:0 6px 20px rgba(34,197,94,.35); }
    .btn{ appearance:none; border:0; border-radius:999px; padding:8px 14px; font-weight:600; cursor:pointer; background:#22c55e; color:#0b1020; }
    .btn-outline{ background:transparent; color:#e5e7eb; border:1px solid rgba(255,255,255,.18); }
    .btn-small{ padding:6px 10px; font-size:13px; border-radius:10px; }
    .btn-danger{ color:#fecaca; border-color:rgba(248,113,113,.35); }
    .btn-danger:hover{ color:#fff; border-color:rgba(248,113,113,.6); box-shadow:0 0 0 2px rgba(248,113,113,.15) inset; }

    /* Login form (admin not logged in) */
    .login-form{ display:flex; flex-direction:column; gap:10px; align-items:stretch; max-width:380px; }
    .login-input{ width:100%; padding:12px 14px; border-radius:10px; border:1px solid rgba(255,255,255,.15); background:#0b132a; color:#fff; font-size:16px; }
    .btn-large{ width:100%; padding:14px 18px; font-size:16px; border-radius:12px; }

    /* MOBILE-FIRST schedules/admin */
    .matches-desktop{ display:none; }
    .matches-mobile{ display:grid; gap:10px; }
    .match-card{ background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.08); border-radius:12px; padding:10px; display:grid; grid-template-columns:1fr auto 1fr; align-items:center; gap:6px; }
    .mc-header{ grid-column:1/-1; display:flex; justify-content:space-between; font-size:12px; color:#cbd5e1; margin-bottom:4px; }
    .team-badge{ --c:#eef; background:var(--c); color:#0b1020; padding:8px 10px; border-radius:10px; font-weight:800; text-align:left; }
    .mc-score{ font-weight:900; font-size:18px; text-align:center; }

    /* Admin responsive */
    .admin input[type=number]{ width:64px; padding:10px 10px; border-radius:10px; border:1px solid rgba(255,255,255,.2); background:#0b132a; color:#fff; font-size:16px; }
    .admin-desktop{ display:none; }
    .admin-mobile{ display:grid; gap:10px; }
    .admin-card{ background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.08); border-radius:12px; padding:10px; display:grid; grid-template-columns:1fr 1fr; align-items:center; gap:8px; }
    .admin-head{ grid-column:1/-1; display:flex; justify-content:space-between; font-size:12px; color:#cbd5e1; margin-bottom:4px; }
    .admin-team{ display:block; width:100%; padding:8px 10px; border-radius:10px; font-weight:800; color:#0b1020; background:var(--c); overflow-wrap:anywhere; }
    .admin-team.right{ text-align:right; }
    .admin-input{ display:flex; justify-content:center; }
    .admin-input input{ width:100%; max-width:120px; text-align:center; }

    /* Admin: scorer inputs */
    details.scorers{ grid-column:1/-1; background:rgba(255,255,255,.02); border:1px solid rgba(255,255,255,.08); border-radius:10px; }
    details.scorers > summary{ list-style:none; cursor:pointer; padding:8px 10px; font-weight:600; color:#e5e7eb; }
    details.scorers > summary::-webkit-details-marker{ display:none; }
    .scorers-wrap{ padding:8px 10px 12px; display:grid; grid-template-columns:1fr; gap:10px; }
    .sc-col{ display:block; }
    .sc-team{ font-size:12px; color:#cbd5e1; margin-bottom:6px; }
    .sc-grid{ display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); gap:6px; }
    .sc-cell{ display:flex; flex-direction:column; gap:4px; font-size:12px; color:#cbd5e1; }
    .sc-cell input{ width:100%; padding:8px 8px; border-radius:8px; border:1px solid rgba(255,255,255,.15); background:#0b132a; color:#fff; text-align:center; }

    @media (min-width:720px){
      .standings-desktop{ display:block !important; }
      .standings-mobile{ display:none !important; }
      .matches-mobile{ display:none; }
      .matches-desktop{ display:block; }
      .admin-desktop{ display:block; } .admin-mobile{ display:none; }
      .scorers-wrap{ grid-template-columns:1fr 1fr; }
    }
  </style>
</head>
<body>
  <header>
    <div class="wrap" style="display:flex; align-items:center; justify-content:space-between; gap:10px">
      <div>
        <h1><?=h($TOURNAMENT_TITLE)?></h1>
        <div class="sub">Spieltag: <?=$TOURNAMENT_DATE?> • 8-Min-Spiele + 2-Min-Wechsel</div>
      </div>
      <div>
        <?php if (!empty($_SESSION['admin'])): ?>
          <a class="btn btn-outline" href="?action=logout">Logout</a>
        <?php else: ?>
          <a class="btn btn-outline" href="?admin=1">Admin</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <main class="wrap" style="padding:16px">
    <div class="grid">

      <!-- COLLAPSIBLE STANDINGS ABOVE SCHEDULE -->
      <details class="card" id="standings">
        <summary>
          <h2 style="margin:0; font-size:18px">Live-Tabellen</h2>
          <span class="pill caret">▲</span>
        </summary>
        <div class="standings-wrap">
          <!-- MOBILE (compact) -->
          <div class="standings-mobile" style="display:block;" id="standingsMobile">
            <h3>Gruppe 5er</h3>
            <div id="tbl-5er-m">
              <?php $i=1; foreach ($tables['5er'] as $row): ?>
                <div class="row">
                  <div class="left"><span class="badge-rank"><?=$i++?></span><span class="teamchip" style="--c: <?=pastel_color_for($row['team'])?>; padding:4px 8px; "><?=h($row['team'])?></span></div>
                  <div class="meta">Pkt <?=$row['pts']?> · TD <?=$row['gd']?></div>
                </div>
              <?php endforeach; if(!$tables['5er']) echo '<div class="muted">Noch keine Ergebnisse.</div>'; ?>
            </div>

            <h3 style="margin-top:12px">Gruppe 6er</h3>
            <div id="tbl-6er-m">
              <?php $i=1; foreach ($tables['6er'] as $row): ?>
                <div class="row">
                  <div class="left"><span class="badge-rank"><?=$i++?></span><span class="teamchip" style="--c: <?=pastel_color_for($row['team'])?>; padding:4px 8px; "><?=h($row['team'])?></span></div>
                  <div class="meta">Pkt <?=$row['pts']?> · TD <?=$row['gd']?></div>
                </div>
              <?php endforeach; if(!$tables['6er']) echo '<div class="muted">Noch keine Ergebnisse.</div>'; ?>
            </div>
          </div>

          <!-- DESKTOP (full table) -->
          <div class="standings-desktop" style="display:none;" id="standingsDesktop">
            <h3>Gruppe 5er</h3>
            <table class="standings-table">
              <thead><tr><th>#</th><th class="col-team">Team</th><th>SP</th><th>S</th><th>U</th><th>N</th><th>TF</th><th>TA</th><th>TD</th><th>Pkt</th></tr></thead>
              <tbody id="tbl-5er">
              <?php $i=1; foreach ($tables['5er'] as $row): ?>
                <tr>
                  <td><?=$i?></td>
                  <td class="col-team"><span class="teamchip" style="--c: <?=pastel_color_for($row['team'])?>; padding:4px 8px; "><?=h($row['team'])?></span></td>
                  <td><?=$row['gp']?></td><td><?=$row['w']?></td><td><?=$row['d']?></td><td><?=$row['l']?></td>
                  <td><?=$row['gf']?></td><td><?=$row['ga']?></td><td><?=$row['gd']?></td><td><?=$row['pts']?></td>
                </tr>
              <?php $i++; endforeach; if(!$tables['5er']) echo '<tr><td colspan="10" class="muted">Noch keine Ergebnisse.</td></tr>'; ?>
              </tbody>
            </table>

            <h3 style="margin-top:16px">Gruppe 6er</h3>
            <table class="standings-table">
              <thead><tr><th>#</th><th class="col-team">Team</th><th>SP</th><th>S</th><th>U</th><th>N</th><th>TF</th><th>TA</th><th>TD</th><th>Pkt</th></tr></thead>
              <tbody id="tbl-6er">
              <?php $i=1; foreach ($tables['6er'] as $row): ?>
                <tr>
                  <td><?=$i?></td>
                  <td class="col-team"><span class="teamchip" style="--c: <?=pastel_color_for($row['team'])?>; padding:4px 8px; "><?=h($row['team'])?></span></td>
                  <td><?=$row['gp']?></td><td><?=$row['w']?></td><td><?=$row['d']?></td><td><?=$row['l']?></td>
                  <td><?=$row['gf']?></td><td><?=$row['ga']?></td><td><?=$row['gd']?></td><td><?=$row['pts']?></td>
                </tr>
              <?php $i++; endforeach; if(!$tables['6er']) echo '<tr><td colspan="10" class="muted">Noch keine Ergebnisse.</td></tr>'; ?>
              </tbody>
            </table>
          </div>
        </div>
      </details>

      <!-- SCORERS LIST (now collapsible & collapsed by default) -->
      <details class="card" id="scorers">
        <summary>
          <h2 style="margin:0; font-size:18px">Torschützenliste</h2>
          <span class="pill caret">▲</span>
        </summary>
        <div class="scorers-public-wrap">
          <div id="scorerList" class="scorer-list">
            <?php
              // server-side initial render (gleich wie compute_scorers)
              $byId = []; foreach($sched_copy as $mm){ $byId[$mm['id']]=$mm; }
              $agg = [];
              if (!isset($state['scorers'])) $state['scorers'] = [];
              foreach ($state['scorers'] as $mid=>$dat){
                if (!isset($byId[$mid])) continue; $mm=$byId[$mid];
                foreach(['t1','t2'] as $side){
                  $team = ($side==='t1')? $mm['t1'] : $mm['t2'];
                  if (!isset($dat[$side])||!is_array($dat[$side])) continue;
                  foreach($dat[$side] as $num=>$cnt){ $cnt=(int)$cnt; if($cnt<=0) continue;
                    $key=$team.'#'.$num;
                    if(!isset($agg[$key])) $agg[$key]=['team'=>$team,'num'=>(int)$num,'goals'=>0];
                    $agg[$key]['goals'] += $cnt;
                  }
                }
              }
              $rows = array_values($agg);
              usort($rows, function($a,$b){ if($a['goals']!==$b['goals']) return $b['goals']-$a['goals']; if($a['team']!==$b['team']) return strcmp($a['team'],$b['team']); return $a['num']-$b['num']; });
              if (!$rows) echo '<div class="muted">Noch keine Torschützen erfasst.</div>';
              else { $i=1; foreach($rows as $r){ $col=pastel_color_for($r['team']); echo '<div class="scorer-row"><div class="left"><span class="badge-rank">'.($i++).'</span><span class="teamchip" style="--c: '.$col.'; padding:4px 8px;">'.h($r['team']).' – #'.$r['num'].'</span></div><div class="meta"><strong>'.$r['goals'].'</strong> Tor'.($r['goals']==1?'':'e').'</div></div>'; } }
            ?>
          </div>
        </div>
      </details>

      <!-- SCHEDULE -->
      <section class="card">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px">
          <h2 style="margin:0; font-size:18px">Spielplan & Ergebnisse</h2>
          <span class="pill">Live</span>
        </div>

        <!-- MOBILE CARDS -->
        <div class="matches-mobile" id="mobileMatches">
          <?php foreach ($sched_copy as $m): $id=$m['id']; $res=$state['results'][$id]??['g1'=>'','g2'=>'']; $sc1=($res['g1']!=='')?(int)$res['g1']:'–'; $sc2=($res['g2']!=='')?(int)$res['g2']:'–'; ?>
            <?php $c1=pastel_color_for($m['t1']); $c2=pastel_color_for($m['t2']); ?>
            <div class="match-card">
              <div class="mc-header"><span>#<?=h($m['slot'])?> • <?=h($m['phase'])?></span><span><?=h($m['start'])?>–<?=h($m['end'])?></span></div>
              <div class="team-badge" style="--c: <?=$c1?>;">
                <?=h($m['t1'])?>
              </div>
              <div class="mc-score" id="s-<?=$id?>"><?=$sc1?> – <?=$sc2?></div>
              <div class="team-badge" style="--c: <?=$c2?>; text-align:right; ">
                <?=h($m['t2'])?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- DESKTOP TABLE -->
        <div class="matches-desktop">
          <table>
            <thead>
              <tr><th class="slot">#</th><th class="time">Start</th><th>Phase</th><th>Team 1</th><th class="score">Tore</th><th class="score">Tore</th><th>Team 2</th></tr>
            </thead>
            <tbody>
              <?php foreach ($sched_copy as $m): $id=$m['id']; $res=$state['results'][$id]??['g1'=>'','g2'=>'']; ?>
                <?php $c1=pastel_color_for($m['t1']); $c2=pastel_color_for($m['t2']); ?>
                <tr>
                  <td class="slot"><?=h($m['slot'])?></td>
                  <td class="time"><?=h($m['start'])?>–<?=h($m['end'])?></td>
                  <td class="muted"><?=h($m['phase'])?></td>
                  <td><span class="teamchip" style="--c: <?=$c1?>; "><?=h($m['t1'])?></span></td>
                  <td class="score" id="s1-<?=$id?>"><?=($res['g1']!=='')? (int)$res['g1'] : '–'?></td>
                  <td class="score" id="s2-<?=$id?>"><?=($res['g2']!=='')? (int)$res['g2'] : '–'?></td>
                  <td style="text-align:right"><span class="teamchip" style="--c: <?=$c2?>; "><?=h($m['t2'])?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="notice" style="margin-top:8px">Tipp: Der runde Button unten aktualisiert die Ergebnisse ohne langes Neuladen.</div>
      </section>

    </div>

    <?php if (isset($_GET['admin'])): ?>
      <section class="card" style="margin-top:12px">
        <h2 style="margin:0 0 10px 0; font-size:18px">Administration</h2>
        <?php if (empty($_SESSION['admin'])): ?>
          <?php if (isset($_GET['err'])) echo '<div class="notice" style="color:#fca5a5">Passwort falsch.</div>'; ?>
          <form method="post" action="?action=login" class="login-form">
            <label for="pw" class="muted">Passwort</label>
            <input id="pw" class="login-input" type="password" name="pw" autocomplete="current-password" />
            <button class="btn btn-large" type="submit">Einloggen</button>
          </form>
        <?php else: ?>
          <?php if (isset($_GET['saved'])) echo '<div class="notice">Gespeichert.</div>'; ?>
          <form class="admin" method="post" action="?action=save">
            <!-- MOBILE ADMIN CARDS -->
            <div class="admin-mobile" id="adminMobile">
              <?php foreach ($sched_copy as $m): $id=$m['id']; $res=$state['results'][$id]??['g1'=>'','g2'=>'']; $sc=$state['scorers'][$id]??['t1'=>[],'t2'=>[]]; ?>
                <div class="admin-card">
                  <div class="admin-head"><span>#<?=h($m['slot'])?> • <?=h($m['phase'])?></span><span><?=h($m['start'])?>–<?=h($m['end'])?></span></div>
                  <span class="admin-team" style="--c: <?=pastel_color_for($m['t1'])?>; "><?=h($m['t1'])?></span>
                  <span class="admin-team right" style="--c: <?=pastel_color_for($m['t2'])?>; "><?=h($m['t2'])?></span>
                  <div class="admin-input"><input type="number" min="0" name="g1_<?=$id?>" value="<?=($res['g1']!=='')?(int)$res['g1']:''?>" /></div>
                  <div class="admin-input"><input type="number" min="0" name="g2_<?=$id?>" value="<?=($res['g2']!=='')?(int)$res['g2']:''?>" /></div>

                  <details class="scorers">
                    <summary>Torschützen erfassen</summary>
                    <div class="scorers-wrap">
                      <div class="sc-col">
                        <div class="sc-team"><?=h($m['t1'])?></div>
                        <div class="sc-grid">
                          <?php for ($n=1;$n<=12;$n++): $val = isset($sc['t1'][$n])?(int)$sc['t1'][$n]:''; ?>
                            <label class="sc-cell">#<?=$n?><input type="number" min="0" name="sc1_<?=$id?>_<?=$n?>" value="<?=$val?>" /></label>
                          <?php endfor; ?>
                        </div>
                      </div>
                      <div class="sc-col">
                        <div class="sc-team" style="text-align:right;"><?=h($m['t2'])?></div>
                        <div class="sc-grid">
                          <?php for ($n=1;$n<=12;$n++): $val = isset($sc['t2'][$n])?(int)$sc['t2'][$n]:''; ?>
                            <label class="sc-cell">#<?=$n?><input type="number" min="0" name="sc2_<?=$id?>_<?=$n?>" value="<?=$val?>" /></label>
                          <?php endfor; ?>
                        </div>
                      </div>
                    </div>
                  </details>

                  <div class="admin-actions" style="grid-column:1/-1; display:flex; justify-content:flex-end;">
                    <button class="btn-outline btn-small btn-danger" name="del" value="<?=$id?>" type="submit" title="Ergebnis & Scorer löschen">✕ Löschen (Ergebnis & Scorer)</button>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <!-- DESKTOP ADMIN TABLE -->
            <div class="admin-desktop" id="adminDesktop">
              <table>
                <thead><tr><th>#</th><th>Zeit</th><th>Phase</th><th>Team 1</th><th>Tore</th><th>Tore</th><th>Team 2</th><th>Aktion</th></tr></thead>
                <tbody>
                  <?php foreach ($sched_copy as $m): $id=$m['id']; $res=$state['results'][$id]??['g1'=>'','g2'=>'']; $sc=$state['scorers'][$id]??['t1'=>[],'t2'=>[]]; ?>
                    <tr>
                      <td class="slot"><?=h($m['slot'])?></td>
                      <td class="time"><?=h($m['start'])?>–<?=h($m['end'])?></td>
                      <td class="muted"><?=h($m['phase'])?></td>
                      <td><span class="teamchip" style="--c: <?=pastel_color_for($m['t1'])?>; padding:4px 8px; "><?=h($m['t1'])?></span></td>
                      <td><input type="number" min="0" name="g1_<?=$id?>" value="<?=($res['g1']!=='')?(int)$res['g1']:''?>" /></td>
                      <td><input type="number" min="0" name="g2_<?=$id?>" value="<?=($res['g2']!=='')?(int)$res['g2']:''?>" /></td>
                      <td style="text-align:right"><span class="teamchip" style="--c: <?=pastel_color_for($m['t2'])?>; padding:4px 8px; "><?=h($m['t2'])?></span></td>
                      <td>
                        <div style="display:flex; gap:6px; align-items:flex-start;">
                          <button class="btn-outline btn-small btn-danger" name="del" value="<?=$id?>" type="submit" title="Ergebnis & Scorer löschen">✕ Löschen</button>
                          <details class="scorers" style="margin-left:6px; min-width:260px;">
                            <summary>Torschützen</summary>
                            <div class="scorers-wrap">
                              <div class="sc-col">
                                <div class="sc-team"><?=h($m['t1'])?></div>
                                <div class="sc-grid">
                                  <?php for ($n=1;$n<=12;$n++): $val = isset($sc['t1'][$n])?(int)$sc['t1'][$n]:''; ?>
                                    <label class="sc-cell">#<?=$n?><input type="number" min="0" name="sc1_<?=$id?>_<?=$n?>" value="<?=$val?>" /></label>
                                  <?php endfor; ?>
                                </div>
                              </div>
                              <div class="sc-col">
                                <div class="sc-team" style="text-align:right;"><?=h($m['t2'])?></div>
                                <div class="sc-grid">
                                  <?php for ($n=1;$n<=12;$n++): $val = isset($sc['t2'][$n])?(int)$sc['t2'][$n]:''; ?>
                                    <label class="sc-cell">#<?=$n?><input type="number" min="0" name="sc2_<?=$id?>_<?=$n?>" value="<?=$val?>" /></label>
                                  <?php endfor; ?>
                                </div>
                              </div>
                            </div>
                          </details>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <div style="display:flex; gap:8px; margin-top:10px">
              <button class="btn" type="submit">Ergebnisse speichern</button>
              <a class="btn btn-outline" href="?admin=1">Neu laden</a>
            </div>
            <div class="muted" style="margin-top:8px">Hinweis: Ergebnisse & Torschützen werden in <code>data.json</code> im selben Ordner gespeichert.</div>
          </form>
        <?php endif; ?>
      </section>
    <?php endif; ?>

  </main>

  <button class="floating-refresh" id="refreshBtn" title="Ergebnisse aktualisieren">⟳</button>

  <script>
    async function softRefresh(){
      try{
        const res = await fetch('index.php?action=state', {cache:'no-store'});
        const data = await res.json();
        // Reset & apply scores
        (data.schedule || []).forEach(m=>{
          document.querySelectorAll('#s1-'+m.id).forEach(el=> el.textContent = '–');
          document.querySelectorAll('#s2-'+m.id).forEach(el=> el.textContent = '–');
          const comb = document.getElementById('s-'+m.id); if (comb) comb.textContent = '– – –';
        });
        Object.entries(data.results || {}).forEach(([id, r])=>{
          const g1 = (r.g1!==''? r.g1 : '–');
          const g2 = (r.g2!==''? r.g2 : '–');
          document.querySelectorAll('#s1-'+id).forEach(el=> el.textContent = g1);
          document.querySelectorAll('#s2-'+id).forEach(el=> el.textContent = g2);
          const comb = document.getElementById('s-'+id); if (comb) comb.textContent = g1+' – '+g2;
        });
        // Tables
        function rowHTML(rows){
          if(!rows||!rows.length) return '<div class="muted">Noch keine Ergebnisse.</div>';
          let i=1; return rows.map(r=>`<div style="display:flex; justify-content:space-between; gap:8px; padding:6px 0; border-bottom:1px dashed rgba(255,255,255,.08)">
            <div style="display:flex; gap:6px; align-items:center"><span class="pill" style="min-width:24px; text-align:center; ">${i++}</span><span class="teamchip" style="--c: ${getPastel(r.team)}; padding:4px 8px;">${r.team}</span></div>
            <div class="muted">Pkt ${r.pts} · TD ${r.gd}</div>
          </div>`).join('');
        }
        function tableHTML(rows){
          if(!rows||!rows.length) return '<tr><td colspan="10" class="muted">Noch keine Ergebnisse.</td></tr>';
          let i=1; return rows.map(r=>`<tr>
            <td>${i++}</td>
            <td><span class="teamchip" style="--c: ${getPastel(r.team)}; padding:4px 8px;">${r.team}</span></td>
            <td>${r.gp}</td><td>${r.w}</td><td>${r.d}</td><td>${r.l}</td>
            <td>${r.gf}</td><td>${r.ga}</td><td>${r.gd}</td><td>${r.pts}</td>
          </tr>`).join('');
        }
        const withColors = g=> (data.tables[g]||[]).map(row=>({ ...row }));
        const t5 = document.getElementById('tbl-5er'); if(t5) t5.innerHTML = tableHTML(withColors('5er'));
        const t6 = document.getElementById('tbl-6er'); if(t6) t6.innerHTML = tableHTML(withColors('6er'));
        const t5m = document.getElementById('tbl-5er-m'); if(t5m) t5m.innerHTML = rowHTML(withColors('5er'));
        const t6m = document.getElementById('tbl-6er-m'); if(t6m) t6m.innerHTML = rowHTML(withColors('6er'));

        // Scorers
        function scorersHTML(list){
          if(!list || !list.length) return '<div class="muted">Noch keine Torschützen erfasst.</div>';
          let i=1; return list.map(r=>`<div class="scorer-row">
            <div class="left"><span class="badge-rank">${i++}</span><span class="teamchip" style="--c: ${getPastel(r.team)}; padding:4px 8px;">${r.team} – #${r.num}</span></div>
            <div class="meta"><strong>${r.goals}</strong> Tor${r.goals===1?'':'e'}</div>
          </div>`).join('');
        }
        const sL = document.getElementById('scorerList'); if (sL) sL.innerHTML = scorersHTML(data.scorers || []);
      }catch(e){ console.warn(e); location.reload(); }
    }
    // Farben-Helper im Frontend: gleiche Farb-Logik pro Schule wie in PHP
    function getPastel(name){
      function baseSchool(str){ return str.replace(/\s+[56]er$/,''); }
      function hashCode(str){let h=0; for(let i=0;i<str.length;i++){h=((h<<5)-h)+str.charCodeAt(i); h|=0;} return Math.abs(h);}
      const base = baseSchool(name);
      const h=Math.abs(hashCode(base))%360; const s=55, l=88; return `hsl(${h} ${s}% ${l}%)`;
    }
    document.getElementById('refreshBtn').addEventListener('click', softRefresh);
    // Disable hidden admin inputs so we don't submit duplicates
    function toggleAdminInputs(){
      const mq = window.matchMedia('(min-width: 720px)');
      const desktop = document.getElementById('adminDesktop');
      const mobile = document.getElementById('adminMobile');
      if (!desktop || !mobile) return;
      const setDisabled = (root, disabled)=> root.querySelectorAll('input[type=number]').forEach(el=> el.disabled = disabled);
      const apply = ()=> { if (mq.matches){ setDisabled(desktop,false); setDisabled(mobile,true); } else { setDisabled(desktop,true); setDisabled(mobile,false); } };
      mq.addEventListener ? mq.addEventListener('change', apply) : mq.addListener(apply);
      apply();
    }
    toggleAdminInputs();
  </script>
</body>
</html>
