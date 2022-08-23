@echo off
mkdir .\views\%1-component

echo ^<p^>%1 works^</p^> > ./views/%1-component/.html
echo /* TODO  */ > ./views/%1-component/.css
echo /* TODO  */ > ./views/%1-component/.js