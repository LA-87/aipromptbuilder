import sys
import tiktoken

# Get the encoding for the model
enc = tiktoken.encoding_for_model("gpt-3.5-turbo")

# Get the text to tokenize from the command line argument
text = sys.argv[1]

# Tokenize the text and get the token count
tokens = enc.encode(text)
token_count = len(tokens)

# Print the token count
print(token_count)
