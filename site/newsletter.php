<?php
require_once 'header.php';

$years = $pdo->query("
  SELECT year, slug, summary
  FROM bsm_newsletter_years
  ORDER BY year ASC
");
$year_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("
  SELECT year, month_num, label, title, slug, summary, emailoctopus_url
  FROM bsm_newsletters
  ORDER BY year ASC, month_num ASC
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" type="text/css" href="styles/newsletter.css">

<h1>BSM Monthly Updates — Complete Archive (Jan 2022 - Aug 2025)</h1>
<p class='muted'>Compiled archive of monthly newsletters. Each entry includes a brief highlight summary. Use the
    table of contents to jump to any month.</p>
<div class='toc'>
    <h2>Table of Contents/Monthly Updates Archive</h2>

    <h3 id='2022'>2022</h3>
    <p><strong>Where it all began.</strong> Well, not quite - this project&apos;s roots go right back to 14th September 1980, but this was the year that I started the newsletter: a fresh focus on using God’s name (YHWH) rather than hiding it behind “LORD,” regular tweaks to make the site easier to read, and a Bible-in-a-year plan to help you stay in the story. Alongside the work were honest, everyday moments—from prayers and chance encounters to Father-Christmas season—that kept everything rooted in real life and real faith.</p>
    <p>If you’re new, these early updates set the tone: open-handed, thankful, and always inviting you to read, question, pray, and journey with me.</p>

<?php
// Group by year
$byYear = [];
foreach ($rows as $r) {
    $byYear[$r['year']][] = $r;
}
?>

<?php foreach ($byYear as $year => $items): ?>
    <h3><?= htmlspecialchars($year ?? '') ?></h3>
    <ul>
        <?php foreach ($items as $it): ?>
            <li>
                <a href="/updates/<?= urlencode($it['slug']) ?>">
                    <?= htmlspecialchars($it['label'] ?? '') ?>
                </a>
                <?php if (!empty($it['emailoctopus_url'])): ?>
                    &nbsp;— <a href="<?= htmlspecialchars($it['emailoctopus_url'] ?? '') ?>" rel="noopener">Original Email</a>
                <?php endif; ?>
                <br><small><?= htmlspecialchars($it['summary'] ?? '') ?></small>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endforeach; ?>
<h2 id='january-2022'>January 2022</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Exploring God's name (YHVH) and the idea of actually using it (ForeverOne) rather than 'LORD'.</li>
        <li>Short video on re-learning to pray the Lord’s Prayer.</li>
        <li>Ongoing website & translation work; invitation to support or help.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='february-2022'>February 2022</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Continued reflection on using 'ForeverOne' for God's name.</li>
        <li>Website: columnar layout for Bible text, Bible reading plan announced.</li>
        <li>Openness to feedback and collaboration.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='march-2022'>March 2022</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Quote from Philip Yancey about writing to find answers; ties to project purpose.</li>
        <li>Travel in Scotland; COVID-19 contraction and recovery.</li>
        <li>Steady progress on website & scriptures.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='april-2022'>April 2022</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Translation notes: 'neighbor' closer to 'friend'.</li>
        <li>Learning Python to manage evolving WEB source text.</li>
        <li>Considering online video chats: 'Further Along The Way' & 'What Even Is The Way?'</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='may-2022'>May 2022</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Project purpose restated (Nielsen Creed: God is God; God is good; I am His and He is mine).</li>
        <li>Separate mailing list for potential discussion groups.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='june-2022'>June 2022</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Starting/previewing off‑the‑cuff video; request for feedback.</li>
        <li>Ongoing planning for discussion groups; continued dev & translation.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='july-2022'>July 2022</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Talk at Business Christians group; deep chats about Scripture's role.</li>
        <li>Encouraging ongoing dialogues; invite to groups.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='august-2022'>August 2022</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Mixed feedback on 'ForeverOne'; learning to hold constructive critique.</li>
        <li>Potential tech help for tools; prayer for resilience.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='september-2022'>September 2022</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Personal losses; reflections on temporary vs permanent (afthartos).</li>
        <li>Updated project description on website.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='october-2022'>October 2022</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Term audits: Congregation/Assembly/People; Prey; Ascents; Leprosy; various phrasing.</li>
        <li>Website menu/security improvements; new profile pic.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='november-2022'>November 2022</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Advent prep via Father Christmas work; reflections on 1 Cor 13:12 (now/then).</li>
        <li>Encouragement to live in dialogue with God.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='december-2022'>December 2022</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Father Christmas encounters; saying 'God bless you' as a meaningful act.</li>
        <li>Considering spiritual impact in everyday interactions.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='january-2023'>January 2023</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Working on software for 3‑text comparison; reading NIV chronologically (David Suchet).</li>
        <li>Tone and interpretation considerations (e.g., Job 38).</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='february-2023'>February 2023</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Accountability rhythms with pastor/friend; challenges: more dramatic readings, avoid word‑study rabbit
            holes.</li>
        <li>Aim: deeper relationship with God over mere technicalities.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='march-2023'>March 2023</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Focus again on the name of God (ForeverOne) in worship contexts (Rev 5:13).</li>
        <li>Invitation to push back or discuss.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='april-2023'>April 2023</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Infinite God as endlessly personal; 'ocean & glass' illustration.</li>
        <li>Encouragement to sense God’s closeness.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='may-2023'>May 2023</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>On drifting subtly; reassurance through worship ('Hold On, Let Go').</li>
        <li>Sense of God 'doing a new thing'.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='june-2023'>June 2023</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Name audit: noticing 'LORD' vs 'Lord' to detect 'Yahweh'.</li>
        <li>Request for observations from readers.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='july-2023'>July 2023</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Back to translating; first draft to Genesis 6.</li>
        <li>Introduce 'MyLord' (adonai) nuance; possible site update soon.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='august-2023'>August 2023</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Holidays & retreat; completed several textual updates (e.g., 'teemed', removed 'it happened that').</li>
        <li>Personal reflections on 'Personal Jesus' and intimacy with God.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='september-2023'>September 2023</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Ruach survey through Job; NT 'Lord' changed to 'ForeverOne' where appropriate.</li>
        <li>Image of God 'clothing' us (Judg 6:34).</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='october-2023'>October 2023</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>WEB comparison to Genesis 24; refining archaic phrasing.</li>
        <li>Use of interlinear & multiple translations for clarity.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='november-2023'>November 2023</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>John 17:22–23 emphasis on shared glory & unity.</li>
        <li>Up to Genesis 28 in WEB improvements; UX idea for book list sorting.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='december-2023'>December 2023</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Driving incident turned into a spiritual lesson on identity & humility.</li>
        <li>Seasonal reflections on becoming God’s children.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='january-2024'>January 2024</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Illness delay; started recording daily Bible readings (Day 30).</li>
        <li>Tweaks emerging from reading aloud; playlist shared.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='february-2024'>February 2024</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Readings up to Day 42; request for feedback on performance style.</li>
        <li>Travel to Scotland/Derby; gratitude for support.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='march-2024'>March 2024</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Prophetic art reflection: 'Peace in the Eye of the Storm'.</li>
        <li>Readings expanded (e.g., Joshua, Judges, Ruth, Matthew).</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='april-2024'>April 2024</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Trip to New Zealand; testimonies of providence; Waihi/Martha Mine reflections.</li>
        <li>YouTube channel releases & prayer for growth metrics.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='may-2024'>May 2024</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Back from NZ; pausing daily readings; joy in editing again.</li>
        <li>Completed Genesis comparison; multiple semi‑automatic modernisations.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='june-2024'>June 2024</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Completed Exodus comparison; test site toggle for translated names (e.g., Hephzibah/MyDelightIsInHer).
        </li>
        <li>Testimony: back healed during prayer; new £10/month supporter.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='july-2024'>July 2024</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Paragraph systems improved; continued test site previews for translated names.</li>
        <li>Ongoing gratitude & feedback requests.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='august-2024'>August 2024</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Faith journey re: car & 'mountain‑moving'; Hebrews 11 & 1 Kings 18 'small cloud'.</li>
        <li>WEB comparison: Galatians, Leviticus 17; tool refinements.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='september-2024'>September 2024</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Wheel replacement as an act of faith; providential 'blocks' story.</li>
        <li>Finished Leviticus; started Numbers; aim to finish Torah by year‑end.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='october-2024'>October 2024</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Sabbath rest focus amidst busy season (Father Christmas & 3D printed percussion).</li>
        <li>Work FROM rest; aligning daily tasks to God's priorities.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='november-2024'>November 2024</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Advent themes (hope, peace, joy, love) & core of love.</li>
        <li>3D‑printed shakers: give‑away leading to £150 donation; reverence for the name (Matt 6:9).</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='december-2024'>December 2024</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Prophetic word from Isabel Skulason; perseverance & destiny.</li>
        <li>Encouragement to step into calling in the new year.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='january-2025'>January 2025</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>WEB 2024 update replaced 'Yahweh' with 'LORD'—software adjustments made.</li>
        <li>Completed Matthew; resumed Numbers comparison.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='february-2025'>February 2025</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Built punctuation‑diff tool to cut through visual noise in WinMerge.</li>
        <li>Punctuation pass complete through Numbers; speeds future work.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='march-2025'>March 2025</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Word studies: 'makarios' → 'happier'; 'anthropos' → 'human/people'.</li>
        <li>Numbers comparison complete; many occurrences ahead.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='april-2025'>April 2025</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>AI‑assisted BibleHarmony app overhaul—clearer and faster.</li>
        <li>James more or less complete; mentor feedback in progress.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='may-2025'>May 2025</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Daughter’s 'spade' picture interpreted as a new tool (possibly AI).</li>
        <li>BibleHarmony becomes editor; named 'CleanSlate Bible' candidate; finished Nehemiah; various lexical
            updates; Rhodes holiday.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='june-2025'>June 2025</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Volunteer PHP dev (Nick from Minnesota) joins—3 improvements already.</li>
        <li>BibleHarmony: save/change detection; Deut 7 done; multiple wording modernisations; Exodus 20:7
            re‑rendered ('carry the name...emptily').</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='july-2025'>July 2025</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>Progress roundup: James, Nehemiah, Mark pass, many lexical tweaks.</li>
        <li>Prayer points; New Wine Festival involvement; neck improvement after prayer.</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>
<hr>
<h2 id='august-2025'>August 2025</h2>
<div class='card'>
    <strong>Highlights</strong>
    <ul>
        <li>New Wine follow‑up: healing/massage for neck.</li>
        <li>Explained 4‑pass translation workflow; spotlighted 3 'Before & After' verses (Gen 1:3; Deut 28:9;
            28:68).</li>
    </ul>
    <p class='muted'>Comments, questions, or pushback are always welcome. If you haven’t yet subscribed, you can do
        so on the BSM site.</p>
</div>

<h2 id='2023'>2023</h2>
<p>Finding the voice of the text. The language grew clearer and closer to the original—less church-jargon, more plain English. You’ll see careful choices (like distinguishing “LORD” from “Lord,” or “MyLord” where the Hebrew points that way), word-studies that serve understanding, and readings that bring the text to life. Through it all runs one thread: precision isn’t the goal; presence is—helping you meet God in the words on the page.</p>
<p>Expect thoughtful reflections alongside practical progress, and plenty of space to agree, disagree, and discover.</p>

<h2 id='2024'>2024</h2>
<p>Opening outward. Daily readings moved onto YouTube, there were stories of timely kindnesses (hello, New Zealand!) and even a healing testimony. On the site, paragraphing became more natural, and you could toggle between translated names (e.g., Hephzibah ↔ “MyDelightIsInHer,” Yahweh ↔ “ForeverOne”), making familiar passages feel fresh without losing their roots.</p>
<p>By year’s end the pace felt both calm and purposeful—Sabbath rest, Advent hope, and a gentle nudge to keep walking with God, together.</p>

<h2 id='2025'>2025 (to August)</h2>
<p>Gaining pace, keeping heart. Behind the scenes, the tools got smoother so the translation could move faster; on the page, the English stayed simple and strong (“happier” instead of “blessed,” “people” where the Greek means everyone). Whole books edged forward, “before & after” snippets showed why small edits matter, and friends rallied to help with the website.</p>
<p>The invitation remains the same: read along, pray with us, and tell us what helps you see - and love - ForeverOne more clearly.</p>
