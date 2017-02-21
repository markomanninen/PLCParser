(eval-and-compile (setv operators []))
; these two method behave a little bit different when using import / require
; actually eval-when-compile loses operators variable
; but on the other hand eval-and-compile, even all seems good
; usage of the reader macro $ doesn't give expected results
;(eval-when-compile (setv operators []))

; add operators to global variable so that on parser loop
; we can use it on if clauses
(defreader > [item]
  (if-not (in item operators)
    (.append operators item)))
