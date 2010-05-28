
#
# Table structure for table 'tx_t3sportsbet_betgames'
#
CREATE TABLE tx_t3sportsbet_betgames (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,

	name varchar(255) DEFAULT '' NOT NULL,
	competition text NOT NULL,
	comment text NOT NULL,

	points_accurate tinyint(4) DEFAULT '0' NOT NULL,
	points_goalsdiff tinyint(4) DEFAULT '0' NOT NULL,
	points_tendency tinyint(4) DEFAULT '0' NOT NULL,
	draw_if_extratime tinyint(4) DEFAULT '0' NOT NULL,
	draw_if_penalty tinyint(4) DEFAULT '0' NOT NULL,
	ignore_greentable tinyint(4) DEFAULT '0' NOT NULL,
	lockminutes int(11) DEFAULT '30' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE tx_t3sportsbet_betsets (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,

	round int(11) DEFAULT '0' NOT NULL,
	round_name varchar(100) DEFAULT '' NOT NULL,

	betgame int(11) DEFAULT '0' NOT NULL,
	status tinyint(4) DEFAULT '0' NOT NULL,
	t3matches int(11) DEFAULT '0' NOT NULL,
	comment text NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

---
-- Matches to betsets
---
CREATE TABLE tx_t3sportsbet_betsets_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);


CREATE TABLE tx_t3sportsbet_bets (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,

	betset int(11) DEFAULT '0' NOT NULL,
	fe_user int(11) DEFAULT '0' NOT NULL,
	t3match int(11) DEFAULT '0' NOT NULL,
	goals_home int(11) DEFAULT '0' NOT NULL,
	goals_guest int(11) DEFAULT '0' NOT NULL,
	points tinyint(11) DEFAULT '0' NOT NULL,
	finished tinyint(4) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY idx_betusr (betset,fe_user)
);


---
-- A possible bet for teams
---
CREATE TABLE tx_t3sportsbet_teamquestions (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,

	betset int(11) DEFAULT '0' NOT NULL,
	question text NOT NULL,
	points tinyint(11) DEFAULT '0' NOT NULL,
	openuntil datetime DEFAULT '0000-00-00 00:00:00'
	teams int(11) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
);

---
-- Team questions to teams 
---
CREATE TABLE tx_t3sportsbet_teamquestions_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);

---
-- User bets for teams 
---
CREATE TABLE tx_t3sportsbet_teambets (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,

	question int(11) DEFAULT '0' NOT NULL,
	fe_user int(11) DEFAULT '0' NOT NULL,
	team int(11) DEFAULT '0' NOT NULL,
	possiblepoints tinyint(11) DEFAULT '0' NOT NULL,
	points tinyint(11) DEFAULT '0' NOT NULL,
	finished tinyint(4) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY idx_teamusr (question,fe_user)
);
