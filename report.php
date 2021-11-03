<?php

/**
 * Use this file to output reports required for the SQL Query Design test.
 * An example is provided below. You can use the `asTable` method to pass your query result to,
 * to output it as a styled HTML table.
 */

$database = 'nba2019';
require_once('vendor/autoload.php');
require_once('include/utils.php');

/*
 * Example Query
 * -------------
 * Retrieve all team codes & names
 */
echo '<h1>Example Query</h1>';
$teamSql = "SELECT * FROM team";
$teamResult = query($teamSql);
// dd($teamResult);
echo asTable($teamResult);

/*
 * Report 1
 * --------
 * Produce a query that reports on the best 3pt shooters in the database that are older than 30 years old. Only 
 * retrieve data for players who have shot 3-pointers at greater accuracy than 35%.
 * 
 * Retrieve
 *  - Player name
 *  - Full team name
 *  - Age
 *  - Player number
 *  - Position
 *  - 3-pointers made %
 *  - Number of 3-pointers made 
 *
 * Rank the data by the players with the best % accuracy first.
 */
echo '<h1>Report 1 - Best 3pt Shooters</h1>';

$best3ptShootersSql = "SELECT 
	r.name,
	t.name AS team_name,
	p.age,
	r.number,
	r.pos,
	CONCAT(CAST(((p.3pt / p.3pt_attempted) * 100) AS DECIMAL(10,2)), '%') AS accuracy,
	p.3pt
	
FROM 
	roster	r,
	player_totals p,
	team t
WHERE
	r.id = p.player_id AND
	r.team_code = t.code AND
	p.age > 30 AND
	(p.3pt / p.3pt_attempted) > 0.35
	
ORDER BY
	accuracy DESC";

$best3ptShootersResult = query($best3ptShootersSql);
echo asTable($best3ptShootersResult);
/*
 * Report 2
 * --------
 * Produce a query that reports on the best 3pt shooting teams. Retrieve all teams in the database and list:
 *  - Team name
 *  - 3-pointer accuracy (as 2 decimal place percentage - e.g. 33.53%) for the team as a whole,
 *  - Total 3-pointers made by the team
 *  - # of contributing players - players that scored at least 1 x 3-pointer
 *  - of attempting player - players that attempted at least 1 x 3-point shot
 *  - total # of 3-point attempts made by players who failed to make a single 3-point shot.
 * 
 * You should be able to retrieve all data in a single query, without subqueries.
 * Put the most accurate 3pt teams first.
 */
echo '<h1>Report 2 - Best 3pt Shooting Teams</h1>';

$best3ptTeamsSql = "SELECT
    t.name,
    CONCAT(CAST((SUM(p.3pt) / SUM(p.3pt_attempted) * 100) AS DECIMAL(10,2)), '%') AS accuracy_3pt,
    SUM(p1.3pt) AS total_3pt,	
    COUNT(DISTINCT(p2.player_id)) AS no_contributing_players,
    COUNT(DISTINCT(p3.player_id)) AS no_attempting_players,
    SUM(p4.3pt_attempted) AS total_3pt_attempts_0_3pt
	
FROM
	team t,
	player_totals p,
    
    roster r 
        LEFT JOIN player_totals p1
            ON r.id = p1.player_id
                
        LEFT JOIN player_totals p2
            ON r.id = p2.player_id AND
                p2.3pt > 0
                
        LEFT JOIN player_totals p3
            ON r.id = p3.player_id AND
                p3.3pt_attempted > 0
                
        LEFT JOIN player_totals p4
            ON r.id = p4.player_id AND
                p4.3pt = 0
            
WHERE
	t.code = r.team_code AND
	r.id = p.player_id
    
GROUP BY
	t.name
    
ORDER BY
	accuracy_3pt DESC";

$best3ptTeamsResult = query($best3ptTeamsSql);
echo asTable($best3ptTeamsResult);
