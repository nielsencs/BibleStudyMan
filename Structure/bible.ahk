#NoEnv                                ; Recommended for performance and compatibility with future AutoHotkey releases.
#Warn                                 ; Enable warnings to assist with detecting common errors.
SendMode Input                        ; Recommended for new scripts due to its superior speed and reliability.
SetWorkingDir %A_ScriptDir%           ; Ensures a consistent starting directory.

MsgBox, Carl's AutoHotKey script for converting the downloaded WEB into MySQL-ready SQL scripts.

WinActivate, TextPad

tt = Old
iSleep = 400

;bookNum = 1
;bookCode = GEN
;book = Genesis
;bookLen = 50             

;bookNum = 2
;bookCode = EXO
;book = Exodus
;bookLen = 40

;bookNum = 3
;bookCode = LEV
;book = Leviticus
;bookLen = 27

;bookNum = 4
;bookCode = NUM
;book = Numbers
;bookLen = 36

;bookNum = 5
;bookCode = DEU
;book = Deuteronomy
;bookLen = 34

;bookNum = 6
;bookCode = JOS
;book = Joshua
;bookLen = 24

;bookNum = 7
;bookCode = JDG
;book = Judges
;bookLen = 21

;bookNum = 8
;bookCode = RUT
;book = Ruth
;bookLen = 4

;bookNum = 9
;bookCode = 1SM
;book = 1_Samuel
;bookLen = 31

;bookNum = 10
;bookCode = 2SM
;book = 2_Samuel
;bookLen = 24

;bookNum = 11
;bookCode = 1KI
;book = 1_Kings
;bookLen = 22

;bookNum = 12
;bookCode = 2KI
;book = 2_Kings
;bookLen = 25

;bookNum = 13
;bookCode = 1CH
;book = 1_Chronicles
;bookLen = 29

;bookNum = 14
;bookCode = 2CH
;book = 2_Chronicles
;bookLen = 36

;bookNum = 15
;bookCode = EZR
;book = Ezra
;bookLen = 10

;bookNum = 16
;bookCode = NEH
;book = Nehemiah
;bookLen = 13

;bookNum = 17
;bookCode = EST
;book = Esther
;bookLen = 10

;bookNum = 18
;bookCode = JOB
;book = Job
;bookLen = 42

;bookNum = 19
;bookCode = PSA
;book = Psalms
;bookLen = 150

;bookNum = 20
;bookCode = PRO
;book = Proverbs
;bookLen = 31

;bookNum = 21
;bookCode = ECC
;book = Ecclesiastes
;bookLen = 12

;bookNum = 22
;bookCode = SON
;book = SongOfSongs
;bookLen = 8

;bookNum = 23
;bookCode = ISA
;book = Isaiah
;bookLen = 66

;bookNum = 24
;bookCode = JER
;book = Jeremiah
;bookLen = 52

;bookNum = 25
;bookCode = LAM
;book = Lamentations
;bookLen = 5

;bookNum = 26
;bookCode = EZE
;book = Ezekiel
;bookLen = 48

;bookNum = 27
;bookCode = DAN
;book = Daniel
;bookLen = 12

;bookNum = 28
;bookCode = HOS
;book = Hosea
;bookLen = 14

;bookNum = 29
;bookCode = JOE
;book = Joel
;bookLen = 3

;bookNum = 30
;bookCode = AMO
;book = Amos
;bookLen = 9

;bookNum = 31
;bookCode = OBA
;book = Obadiah
;bookLen = 1

;bookNum = 32
;bookCode = JON
;book = Jonah
;bookLen = 4

;bookNum = 33
;bookCode = MIC
;book = Micah
;bookLen = 7

;bookNum = 34
;bookCode = NAH
;book = Nahum
;bookLen = 3

;bookNum = 35
;bookCode = HAB
;book = Habakkuk
;bookLen = 3

;bookNum = 36
;bookCode = ZEP
;book = Zephaniah
;bookLen = 3

;bookNum = 37
;bookCode = HAG
;book = Haggai
;bookLen = 2

;bookNum = 38
;bookCode = ZEC
;book = Zechariah
;bookLen = 14

;bookNum = 39
;bookCode = MAL
;book = Malachi
;bookLen = 4

;tt = New

;bookNum = 40
;bookCode = MAT
;book = Matthew
;bookLen = 28

;bookNum = 41
;bookCode = MAR
;book = Mark
;bookLen = 16

;bookNum = 42
;bookCode = LUK
;book = Luke
;bookLen = 24

;bookNum = 43
;bookCode = JOH
;book = John
;bookLen = 21

;bookNum = 44
;bookCode = ACT
;book = Acts
;bookLen = 28

;bookNum = 45
;bookCode = ROM
;book = Romans
;bookLen = 16

;bookNum = 46
;bookCode = 1CO
;book = 1_Corinthians
;bookLen = 16

;bookNum = 47
;bookCode = 2CO
;book = 2_Corinthians
;bookLen = 13

;bookNum = 48
;bookCode = GAL
;book = Galatians
;bookLen = 6

;bookNum = 49
;bookCode = EPH
;book = Ephesians
;bookLen = 6

;bookNum = 50
;bookCode = PHP
;book = Philippians
;bookLen = 4

;bookNum = 51
;bookCode = COL
;book = Colossians
;bookLen = 4

;bookNum = 52
;bookCode = 1TH
;book = 1_Thessalonians
;bookLen = 5

;bookNum = 53
;bookCode = 2TH
;book = 2_Thessalonians
;bookLen = 3

;bookNum = 54
;bookCode = 1TI
;book = 1_Timothy
;bookLen = 6

;bookNum = 55
;bookCode = 2TI
;book = 2_Timothy
;bookLen = 4

;bookNum = 56
;bookCode = TIT
;book = Titus
;bookLen = 3

;bookNum = 57
;bookCode = PHM
;book = Philemon
;bookLen = 1

;bookNum = 58
;bookCode = HEB
;book = Hebrews
;bookLen = 13

;bookNum = 59
;bookCode = JAM
;book = James
;bookLen = 5

;bookNum = 60
;bookCode = 1PE
;book = 1_Peter
;bookLen = 5

;bookNum = 61
;bookCode = 2PE
;book = 2_Peter
;bookLen = 3

;bookNum = 62
;bookCode = 1JO
;book = 1_John
;bookLen = 5

;bookNum = 63
;bookCode = 2JO
;book = 2_John
;bookLen = 1

;bookNum = 64
;bookCode = 3JO
;book = 3_John
;bookLen = 1

;bookNum = 65
;bookCode = JDE
;book = Jude
;bookLen = 1

;bookNum = 66
;bookCode = REV
;book = Revelation
;bookLen = 22

Send, ^n
Send, {F12}
Sleep, %iSleep%
Send, %A_ScriptDir%\bibleVerses_%bookNum%_%bookCode%.sql.txt

chapter := 1
^!n::
  ; prepare text
  Send, INSERT INTO verses (bookCode, chapter, verseNumber, verseText) VALUES ('
  Send, %bookCode%
  Send, ',
  Send, {space}
  if(chapter<10){
    Send, {space}
  }
  Send, %chapter%, 
  Send, {space}
  Send, +{home}
  Send, ^x

  ; open the next file
  Send, ^o
  Sleep, %iSleep%
  Send, E:\BibleStudy\RestlessFlag-master\WorldEnglishBible\%tt%Testament\%book%\%book%
  if(chapter<10){
    Send, 0
  }
  Send, %chapter%.txt
  Send, !o

  ; do the first macro
  Send, !m
  Send, {down 5}
  Send, {enter}
  Sleep, %iSleep%

  ; do the second macro
  Send, !m
  Send, {down 6}
  Send, {enter}
  Sleep, %iSleep%

  ; do 9 spaces
  Send, ^{home}
  Sleep, %iSleep%
  Send, {space}
  Send, {down}
  Send, {space}
  Send, {down}
  Send, {space}
  Send, {down}
  Send, {space}
  Send, {down}
  Send, {space}
  Send, {down}
  Send, {space}
  Send, {down}
  Send, {space}
  Send, {down}
  Send, {space}
  Send, {down}
  Send, {space}
  Sleep, %iSleep%
  Send, ^{home}

  ; do the fourth macro
  Send, !m
  Send, {down 8}
  Send, {enter}
  Sleep, %iSleep%

  ; cut and paste into SQL file
  Send, ^a
  Send, ^x
  Send, ^{F4}
  Send, n
  Send, ^v

  ; get ready for the next one
  Send, {down}
  Send, +{end}
  Send, ^x
  Send, {return}

  chapter ++
return

;RunWait, TextPad
;Sleep, 3000
;Send, {space}
