; require > reader macro
(require [hyPLCParser.operators [*]])
; import operator list : operators.hy is expanded and executed first
; so that operators variable is found in the current scope
; setting variable in the same file by setv or eval-and-compile 
; didn't work
(import (hyPLCParser.operators (*)))

; define math operands
(setv ⊤ 1)
(setv ⊥ 0)

; define operator function and math alias
; plus set them to operators global list
(defmacro defoperator [op-name op-symbol params &rest body]
  `(do 
    (defn ~op-name ~params ~@body)
    #>~op-name
    (setv ~op-symbol ~op-name)
    #>~op-symbol))

; define true comparison function
(defn true? [value] 
  (or (= value 1) (= value True) (= value "True")  (= value "⊤")))

; same as nor at the moment... not? is a reserved word
(defoperator nope? ¬ [&rest truth-list] 
  (not (any truth-list)))

; and operation : zero or more arguments, zero will return false, 
; otherwise all items needs to be true
(defoperator and? ∧ [&rest truth-list]
  (all (map true? truth-list)))

; negation of and
(defoperator nand? ↑ [&rest truth-list]
  (not (apply and? truth-list)))

; or operation : zero or more arguments, zero will return false, 
; otherwise at least one of the values needs to be true
(defoperator or? ∨ [&rest truth-list]
  (any (map true? truth-list)))

; negation of or
(defoperator nor? ↓ [&rest truth-list]
  (not (apply or? truth-list)))

; xor operation (parity check) : zero or more arguments, zero will return false, 
; otherwise odd number of true's is true
(defoperator xor? ⊕ [&rest truth-list]
    (setv boolean False)
    (for [truth-value truth-list]
        (if (true? truth-value)
            (setv boolean (not boolean))))
    boolean)

; negation of xor
(defoperator xnor? ↔ [&rest truth-list]
  (not (apply xor? truth-list)))

(defn operand? [x]
  (or 
    (numeric? x)
    (in x ["1" "True" "⊤" "0" "False" "⊥"])))

; main parser loop for propositional logic clauses
; todo: operator precedence!
(defreader $ [code]
  (if
    ; if scalar value, return that
    (not (coll? code)) code
    ; else if empty list, return false
    (= (len code) 0) False
    ; else if list with lenght of 1 and that single item is not the operator
    (and (= (len code) 1) (not (in (get code 0) operators)))
      `#$~@code
    ; else if list with three items, and the operator is in the middle (infix)
    (and (= (len code) 3) (in (get code 1) operators))
      `(~(get code 1) #$~(get code 0) #$~(get code 2))
    ; else if list with two or more items, and the second is the operator
    (and (> (len code) 2) (in (get code 1) operators))
      (do
        ; take first two items and reverse
        (setv a (doto (list (take 2 code)) (.reverse)))
        ; take rest of the items after second item
        (setv b (list (drop 2 code)))
        ; b could be empty
        (if (> (len b) 0)
          `(~@a #$~b)
          `(~@a)))
    ; else if list with more items than 1 and the first item the operator
    (and (> (len code) 1) (in (get code 0) operators))
      (do
        ; take the first item i.e. operator
        (setv a (list (take 1 code)))
        ; append all consequencing operands after operator and flatten list
        (.append a (list (take-while operand? (drop 1 code))))
        (setv a (flatten a))
        ; after the first item seek next items after consequencing operands
        (setv b (list (drop-while operand? (drop 1 code))))
        ; b could be empty
        (if (> (len b) 0)
          `(~@a #$(~@b))
          `(~@a)))
    ; possibly syntax error on clause
    `(print "Expression error!")))
