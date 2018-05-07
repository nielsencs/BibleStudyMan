#NoEnv                                ; Recommended for performance and compatibility with future AutoHotkey releases.
#Warn                                 ; Enable warnings to assist with detecting common errors.
SendMode Input                        ; Recommended for new scripts due to its superior speed and reliability.
SetWorkingDir %A_ScriptDir%           ; Ensures a consistent starting directory.

MsgBox, Carl's AutoHotKey script for converting the downloaded WEB into MySQL-ready SQL scripts.

WinActivate, TextPad

doBook("Old", 1, "GEN", "Genesis", 50)
doBook("Old", 2, "EXO", "Exodus", 40)
doBook("Old", 3, "LEV", "Leviticus", 27)
doBook("Old", 4, "NUM", "Numbers", 36)
doBook("Old", 5, "DEU", "Deuteronomy", 34)
doBook("Old", 6, "JOS", "Joshua", 24)
doBook("Old", 7, "JDG", "Judges", 21)
doBook("Old", 8, "RUT", "Ruth", 4)
doBook("Old", 9, "1SM", "1_Samuel", 31)
doBook("Old", 10, "2SM", "2_Samuel", 24)
doBook("Old", 11, "1KI", "1_Kings", 22)
doBook("Old", 12, "2KI", "2_Kings", 25)
doBook("Old", 13, "1CH", "1_Chronicles", 29)
doBook("Old", 14, "2CH", "2_Chronicles", 36)
doBook("Old", 15, "EZR", "Ezra", 10)
doBook("Old", 16, "NEH", "Nehemiah", 13)
doBook("Old", 17, "EST", "Esther", 10)
doBook("Old", 18, "JOB", "Job", 42)
doBook("Old", 19, "PSA", "Psalms", 150)
doBook("Old", 20, "PRO", "Proverbs", 31)
doBook("Old", 21, "ECC", "Ecclesiastes", 12)
doBook("Old", 22, "SON", "SongOfSongs", 8)
doBook("Old", 23, "ISA", "Isaiah", 66)
doBook("Old", 24, "JER", "Jeremiah", 52)
doBook("Old", 25, "LAM", "Lamentations", 5)
doBook("Old", 26, "EZE", "Ezekiel", 48)
doBook("Old", 27, "DAN", "Daniel", 12)
doBook("Old", 28, "HOS", "Hosea", 14)
doBook("Old", 29, "JOE", "Joel", 3)
doBook("Old", 30, "AMO", "Amos", 9)
doBook("Old", 31, "OBA", "Obadiah", 1)
doBook("Old", 32, "JON", "Jonah", 4)
doBook("Old", 33, "MIC", "Micah", 7)
doBook("Old", 34, "NAH", "Nahum", 3)
doBook("Old", 35, "HAB", "Habakkuk", 3)
doBook("Old", 36, "ZEP", "Zephaniah", 3)
doBook("Old", 37, "HAG", "Haggai", 2)
doBook("Old", 38, "ZEC", "Zechariah", 14)
doBook("Old", 39, "MAL", "Malachi", 4)

doBook("New", 40, "MAT", "Matthew", 28)
doBook("New", 41, "MAR", "Mark", 16)
doBook("New", 42, "LUK", "Luke", 24)
doBook("New", 43, "JOH", "John", 21)
doBook("New", 44, "ACT", "Acts", 28)
doBook("New", 45, "ROM", "Romans", 16)
doBook("New", 46, "1CO", "1_Corinthians", 16)
doBook("New", 47, "2CO", "2_Corinthians", 13)
doBook("New", 48, "GAL", "Galatians", 6)
doBook("New", 49, "EPH", "Ephesians", 6)
doBook("New", 50, "PHP", "Philippians", 4)
doBook("New", 51, "COL", "Colossians", 4)
doBook("New", 52, "1TH", "1_Thessalonians", 5)
doBook("New", 53, "2TH", "2_Thessalonians", 3)
doBook("New", 54, "1TI", "1_Timothy", 6)
doBook("New", 55, "2TI", "2_Timothy", 4)
doBook("New", 56, "TIT", "Titus", 3)
doBook("New", 57, "PHM", "Philemon", 1)
doBook("New", 58, "HEB", "Hebrews", 13)
doBook("New", 59, "JAM", "James", 5)
doBook("New", 60, "1PE", "1_Peter", 5)
doBook("New", 61, "2PE", "2_Peter", 3)
doBook("New", 62, "1JO", "1_John", 5)
doBook("New", 63, "2JO", "2_John", 1)
doBook("New", 64, "3JO", "3_John", 1)
doBook("New", 65, "JDE", "Jude", 1)
doBook("New", 66, "REV", "Revelation", 22)

;doBook(tt, bookNum, bookCode, book, bookLen){
  iSleep = 400

;  Send, ^n
;  Send, {F12}
;  Sleep, %iSleep%
;  ;Send, E:\BibleStudy\RestlessFlag-master\Structure\bibleVerses_%bookNum%_%bookCode%.sql.txt
;  Send, %A_ScriptDir%\bibleVerses_%bookNum%_%bookCode%.sql.txt
;  Send, {enter}

  doChapters(tt, bookNum, bookCode, book, bookLen, iSleep)
  Send, ^s
  Send, ^{F4}
}

;^!n::
doChapters(tt, bookNum, bookCode, book, bookLen, iSleep){
  chapter := 1
  while(chapter <= bookLen){
 
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
}
}
;return

;RunWait, TextPad
;Sleep, 3000
;Send, {space}
