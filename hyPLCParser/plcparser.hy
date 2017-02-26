; require > and < reader macros
(require [hyPLCParser.operators [*]])
; import operator list : operators.hy is expanded and executed first
; so that operators variable is found in the current scope
; setting variable in the same file by setv or eval-and-compile 
; didn't work. this will also import operators-precedence list
(import (hyPLCParser.operators (*)))

; define math operands
(setv ⊤ 1)
(setv ⊥ 0)

; define operator function and math alias (op-symbol)
; plus set them to operators global list
(defmacro defoperator [op-name op-symbol params &rest body]
  `(do 
    (defn ~op-name ~params ~@body)
    #>~op-name
    (setv ~op-symbol ~op-name)
    #>~op-symbol))

; add custom or native operators to the list
; somebody might like this syntax more than using
; reader macro directly. so calling (defoperators + - * /)
; is same as calling #>[+ - * /]
(defmacro defoperators [&rest args] `#>~args)

; define true comparison function
(defn true? [value] 
  (or (= value 1) (= value True)))

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

;synonym for xor
(setv ↮ xor?)
#>↮

; negation of xor
(defoperator xnor? ↔ [&rest truth-list]
  (not (apply xor? truth-list)))

; equivalence
; https://en.wikipedia.org/wiki/Logical_equivalence
; with two values same as xnor but with more values
; result differs: [1 1 1] = True = [0 0 0]
(defoperator eqv? ≡ [&rest truth-list]
  (setv boolean (if (pos? (len truth-list)) True False))
  (for [truth-value truth-list]
    (if (not? truth-value (first truth-list))
      (do (setv boolean False) (break))))
  boolean)

; unquivalence
(defoperator neqv? ≢ [&rest truth-list]
  (not (apply eqv? truth-list)))

; Four implications macro
; Behaviour:
; (1 op 0 op 0) -> (op 1 0 0 ) -> (op (op 1 0) 0)
; Tests:
; (for [y (range 2)]
;   (print "(→ y) =>" (x y)))
; (for [y (range 2)]
;   (for [z (range 2)]
;     (print (% "(op %s" y) (% "%s) =>" z) (x y z))))
; Also note that [(op 1) (op 0)] = [True, False]
(defmacro defimplication [op-name op-symbol func]
  `(defoperator ~op-name ~op-symbol [&rest truth-list]
  (do 
    ; passed arguments is a tuple 
    ; so it needs to be cast to list for pop
    (setv args (list truth-list))
    (if (= (len args) 1) (true? (first args))
      ; else
      (do
        ; default return value is False
        (setv result False)
        ; take the first element of list and remove it
        (setv prev (first args))
        (.remove args prev)
        ; loop over all args
        (while
          (pos? (len args))
          (do
            ; there are at least two items on a list at the moment
            ; so we can get the next and remove it too
            (setv next (first args))
            (.remove args next)
            ; recurisvely get the result. previous could be a list as
            ; well as next could be a list, thus prev needs to be evaluated
            ; at least once more.
            (setv result ~func)
            ;(print 'prev prev 'next next 'result result)
            ; and set result for the previous one
            (setv prev result)))
        ; return resulting boolean value
        result)))))

; Converse implication (P ∨ ¬Q)
; https://en.wikipedia.org/wiki/Converse_implication
(defimplication cimp? ← (any [(← prev) (not (← next))]))

; Material nonimplication (P ∧ ¬Q)
; https://en.wikipedia.org/wiki/Material_nonimplication
(defimplication mnimp? ↛ (all [(↛ prev) (not (↛ next))]))

; Converse nonimplication (¬P ∨ Q)
; https://en.wikipedia.org/wiki/Converse_nonimplication
(defimplication cnimp? ↚ (any [(not (↚ prev)) (↚ next)]))

; Material implication (¬P ∧ Q)
; https://en.wikipedia.org/wiki/Material_conditional
(defimplication mimp? → (all [(not (→ prev)) (→ next)]))

; more function for 
(eval-and-compile
  ; this takes a list of items at least 3
  ; index must be bigger than 1 and smaller than the length of the list
  ; left and right side of the index will be picked to a new list where
  ; centermost item is moved to left and left to center
  ; [1 a 2 b 3 c 4] idx=3 -> [1 a [b 2 3] c 4]
  (defn list-nest [lst idx]
    (setv tmp
      (doto 
        (list (take 1 (drop idx lst))) 
        (.append (get lst (dec idx))) 
        (.append (get lst (inc idx)))))
    (doto 
      (list (take (dec idx) lst))
      (.append tmp)
      (.extend (list (drop (+ 2 idx) lst)))))
  
  (defn one-not-operator? [code]
    (and (= (len code) 1) (not (in (first code) operators))))

  (defn second-operator? [code]
    (and (pos? (len code)) (in (second code) operators)))
  
  (defn first-operator? [code]
    (and (> (len code) 1) (in (first code) operators)))
  
  (defn third [lst] 
    (get lst 2)))

; macro to change precedence order of the operations.
; argument list will be passed to the #< readermacro which 
; will reset arguments to a new operators-precedence list
; example: (defprecedence and? xor? or?)
; or straight to reader macro way: #<[and? xor? or?]
;
; note that calling this macro will empty the previous list of precedence!
; to keep the previous set one should do something like:
; (defprecedence (doto operators-precedence (.extend [operator-x operator-y ...])))
;
; call (defprecedence) to empty the list to the default state
; in that case left-wise order of precedence is used when evaluating
; the list of propositional logic or other symbols
(defmacro defprecedence [&rest args] `#<~args)

; macro that takes mixed prefix and infix notation clauses
; for evaluating their value. this is same as calling
; $ reader macro directly but might be more convenient way
; inside lips code to use than reader macro syntax
; there is no need to use parentheses with this macro
(defmacro defmixfix [&rest items] `#$~items)

; pass multiple (n) evaluation clauses. each of the must be
; wrapped by () parentheses
(defmacro defmixfix-n [&rest items]
  (list-comp `#$~item [item items]))

; main parser loop for propositional logic clauses
(defreader $ [code]
  (if
    ; scalar value
    (not (coll? code)) code
    ; empty list
    (zero? (len code)) False
    ; list with lenght of 1 and the single item not being the operator
    (one-not-operator? code) `#$~@code
    ; list with three or more items, second is the operator
    (second-operator? code)
      (do 
        ; the second operator on the list is the default index
        (setv idx 1)
        ; loop over all operators
        (for [op operators-precedence]
          ; set new index if operator is found from the code and break in that case
          (if (in op code) (do (setv idx (.index code op)) (break))))
        ; make list nested based on the found index and evaluate again
        `#$~(list-nest code idx))
    ; list with more than 1 items and the first item is the operator
    (first-operator? code)
      ; take the first item i.e. operator and use
      ; rest of the items as arguments once evaluated by #$
      `(~(first code) ~@(list-comp `#$~part [part (drop 1 code)]))
    ; possibly syntax error on clause
    ; might be caused by arbitrary usage of operators and operands
    ; something like: (1 1 and? 0 and?)
    `(raise (Exception "Expression error!"))))
