; these two method behave a little bit different when using import / require
; actually eval-when-compile loses operators variable
;(eval-when-compile (setv operators []))
(eval-and-compile 
  (setv operators []
        operators-precedence []))

; add operators to global variable so that on a parser loop
; we can use it on if clauses
; for singular usage: #>operator
; for multiple: #>[operator1 operator 2 ...]
(defreader > [items] 
  (do
    ; transforming singular value to a list for the next for loop
    (if (not (coll? items)) (setv items [items]))
    (for [item items]
      ; discard duplicates
      (if-not (in item operators)
        (.append operators item)))))

; set the order of precedence for oprators
; for singular usage: #<operator
; for multiple: #<[operator1 operator 2 ...]
; note that calling this macro will empty the previous list of precedence!
(defreader < [items]
  (do
    ; (setv operators-precedence []) is not working here
    ; for some macro evaluation - complilation order reason
    ; so emptying the current operators-precedence list more verbose way
    (if (pos? (len operators-precedence))
      (while (pos? (len operators-precedence))
        (.pop operators-precedence)))
    ; transforming singular value to a list for the next for loop
    (if (not (coll? items)) (setv items [items]))
    (for [item items]
      ; discard duplicates
      (if-not (in item operators-precedence)
        (.append operators-precedence item)))))
