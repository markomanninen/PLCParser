#!/usr/bin/python3
# -*- coding: utf-8 -*-
#
# Credits for the starting point of the magic:
# https://github.com/yardsale8/hymagic/blob/master/hymagic/__init__.py
# and special mentions to
# Ryan (https://github.com/kirbyfan64) and 
# Tuukka (https://github.com/tuturto) in hylang-discuss: 
# https://groups.google.com/forum/#!forum/hylang-discuss
# to make it possible for me to resolve all essential obstacles
# when struggling with macros

from IPython.core.magic import Magics, magics_class, line_cell_magic
import ast

try:
    from hy.lex import LexException, PrematureEndOfInput, tokenize
    from hy.compiler import hy_compile, HyTypeError
    from hy.importer import ast_compile
except ImportError as e:
    print("To use this magic extension, please install Hy (https://github.com/hylang/hy) with: pip install git+https://github.com/hylang/hy.git")
    from sys import exit
    exit(e)

print("Use for example: %plc (1 and? 1)")
print("Operators available: nope? ¬ and? ∧ xor? ⊕ or? ∨ nand? ↑ nxor? ↔ nor? ↓")
print("Operands available: True 1 ⊤ False 0 ⊥")

hy_program = """

; these two method behave a little bit different when using import / require
; actually eval-when-compile loses operators variable
; but on the other hand eval-and-compile, even all seems good
; usage of the reader macro $ doesn't give expected results
;(eval-when-compile (setv operators []))
(eval-and-compile (setv operators []))

; define math operands
(setv ⊤ 1)
(setv ⊥ 0)

; add operators to global variable so that on parser loop
; we can use it on if clauses
(defreader > [item]
  (if-not (in item operators)
    (.append operators item)))

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
  (in x [⊤ True 1 "1" "True" "⊤" ⊥ False 0 "0" "False" "⊥"]))

; main parser loop for propositional logic clauses
; todo: operator precedence
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
        ; append all numeric items after operator to a and flatten list
        (.append a (list (take-while 
          ; support numeric operands for math equations
          (fn [x] (or (numeric? x) (in x ["1" "True" "⊤" "0" "False" "⊥"]))) (drop 1 code))))
        (setv a (flatten a))
        ; after the first items seek non numeric i.e. list
        (setv b (list (drop-while 
          ; support numeric operands for math equations
          (fn [x] (or (numeric? x) (in x ["1" "True" "⊤" "0" "False" "⊥"]))) (drop 1 code))))
        ; b could be empty
        (if (> (len b) 0)
          `(~@a #$(~@b))
          `(~@a)))
    ; else possibly syntax error on clause
    `(print "Expression error!")))

; add input here
%s

"""

def get_tokens(source, filename):
    try:
        return tokenize(source)
    except PrematureEndOfInput as e:
        print(e)
    except LexException as e:
        if e.source is None:
            e.source = source
            e.filename = filename
        print(e)

def parse(tokens, source, filename, shell, interactive):
    try:
        _ast = hy_compile(tokens, "__console__", root = interactive)
        shell.run_ast_nodes(_ast.body, filename, compiler = ast_compile)
    except HyTypeError as e:
        if e.source is None:
            e.source = source
            e.filename = filename
        print(e)
    except Exception:
        shell.showtraceback()

@magics_class
class PLCMagics(Magics):
    """ 
    Jupyter Notebook Magics (%plc and %%plc) for Propositional Logic Clauses (PLC) 
    written in Hy language (Lispy Python).
    """
    def __init__(self, shell):
        super(PLCMagics, self).__init__(shell)
    
    @line_cell_magic
    def plc(self, line = None, cell = None, filename = '<input>'):
        # both line %plc and cell %%plc magics are prepared here.
        # if line magic is used then we prepend code #$ reader macro
        # to enable prefix hy code evaluation
        source = hy_program % ("#$%s" % line if line else cell)
        # get input tokens for compile
        tokens = get_tokens(source, filename)
        if tokens:
            return parse(tokens, source, filename, self.shell, ast.Interactive)

def load_ipython_extension(ip):
    """ Load the extension in Jupyter. """
    ip.register_magics(PLCMagics)
