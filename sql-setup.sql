/*
planner -> week
  - daypart

  - docent1
  - docent2 | NONE

  - klas1jaar
  - klas1niveau
  - klas1nummer

  - klas2jaar
  - klas2niveau
  - klas2nummer

  - lokaal1
  - lokaal2 | NONE

  - laptops | NONE

  - notes | NONE


  - USER
  - TIME
  - IP


SQL:
*/
CREATE TABLE `week` (
  `daypart` varchar(4) NOT NULL COMMENT 'day and part of day',
  `docent1` varchar(16) NOT NULL,
  `docent2` varchar(16) NOT NULL,
  `klas1jaar` int(4) NOT NULL,
  `klas1niveau` varchar(16) NOT NULL,
  `klas1nummer` int(4) NOT NULL,
  `klas2jaar` int(4) NOT NULL,
  `klas2niveau` varchar(16) NOT NULL,
  `klas2nummer` int(4) NOT NULL,
  `lokaal1` varchar(16) NOT NULL,
  `lokaal2` varchar(16) NOT NULL,
  `laptops` varchar(32) NOT NULL COMMENT 'laptops',
  `notes` varchar(128) NOT NULL COMMENT 'notes',
  `USER` varchar(16) NOT NULL COMMENT 'user who added entry',
  `TIME` timestamp(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) COMMENT 'timestamp when entry was added',
  `IP` varchar(64) NOT NULL COMMENT 'ip from where entry was added',
  `ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `week`
  ADD PRIMARY KEY (`ID`);
ALTER TABLE `week`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
COMMIT;
/*
planner -> users
  - username
  - password
  - role
  - userLVL
  - lastLoginTime
  - lastLoginIP
  - ID

SQL:
*/
CREATE TABLE `users` (
  `username` varchar(64) NOT NULL,
  `password` varchar(128) NOT NULL,
  `role` varchar(16) NOT NULL,
  `userLVL` int(1) NOT NULL,
  `userAvailability` varchar(64) NOT NULL,
  `lastLoginTime` timestamp(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `lastLoginIP` varchar(64) NOT NULL,
  `ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`);
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
COMMIT;
/*
planner -> klassen
  - jaar
  - niveau
  - klasnummer
  - created
  - ID

SQL:
*/
CREATE TABLE `klassen` (
  `jaar` int(4) NOT NULL,
  `niveau` varchar(16) NOT NULL,
  `nummer` int(4) NOT NULL,
  `created` timestamp(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `klassen`
  ADD PRIMARY KEY (`ID`);
ALTER TABLE `klassen`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
COMMIT;



/*
planner -> lokalen
  - lokaal
  - created
  - ID
*/
CREATE TABLE `lokalen` (
  `lokaal` varchar(16) NOT NULL,
  `created` timestamp(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  `ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `lokalen`
  ADD PRIMARY KEY (`ID`);
ALTER TABLE `lokalen`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
COMMIT;
