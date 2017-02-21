# credits for the starting point of the magic:
# https://github.com/yardsale8/hymagic/blob/master/hymagic/__init__.py
from IPython.core.magic import Magics, magics_class, line_cell_magic
import ast

try:
    import hy
except ImportError as e:
    print("To use this magic extension, please install Hy (https://github.com/hylang/hy) with: pip install git+https://github.com/hylang/hy.git")
    from sys import exit
    exit(e)

print("Use for example: %plc (1 and? 1)")

from hy.lex import LexException, PrematureEndOfInput, tokenize
from hy.compiler import hy_compile, HyTypeError
from hy.importer import ast_compile

hy_program = """

(setv operators [])

(defreader $ [code]
  (print code))

#$%s

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
    """ Jupyter Notebook Magics (%plc and %%plc) for the Propositional Logic Clauses (PLC) 
        written in Hy language (Lispy Python).
    """
    def __init__(self, shell):
        super(PLCMagics, self).__init__(shell)
    
    @line_cell_magic
    def plc(self, line = None, cell = None, filename = '<input>'):
        """  """
        source = hy_program % (line if line else cell)
        # get input tokens for compile
        tokens = get_tokens(source, filename)
        if tokens:
            return parse(tokens, source, filename, self.shell, ast.Interactive)

def load_ipython_extension(ip):
    """ Load the extension in Jupyter. """
    ip.register_magics(PLCMagics)
