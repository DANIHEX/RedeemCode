# RedeemCode
Gemerate redeem codes and run commands when someone uses them.
# RedeemCode
Generate redeem codes and run commands when someone uses them as a gift

# Usage
To generate a new redeem code use command `/generatecode <code: text>` and just type your favorite code in **code** argument.
After sending the command a ui will open. In the first input type the command you want to execute when someone uses the code and for the second input you have 2 options: -1 and a number bigger than 0. -1 makes the code infinite and it will never expire until you update it and make it finite, And a number bigger than 0 means players can use this command for limited times.
Now submit the form, If you didn't do something wrong the code will successfuly be generated.
**Note: To updated a code just generate it again and changes will apply automaticaly**

To use a redeem code players should use command `/redeem <code: text>` and type the code in **code** argument.
If code exists and has not expired the command that you chose when you generated the code will execute.

# Versions
Version | Information | Date | Api
------- | ----------- | ---- | ---
v1.0.0 | First stable version | 3 Aug 2021 | 3.0.0 -> 3.22.1
